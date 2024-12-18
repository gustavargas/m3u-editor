<?php

namespace App\Jobs;

use App\Models\Channel;
use App\Models\Playlist;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use zikwall\m3ucontentparser\M3UContentParser;
use zikwall\m3ucontentparser\M3UItem;

class ProcessM3uImport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     * 
     * @param Playlist $playlist
     */
    public function __construct(
        public Playlist $playlist
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Update the playlist status to processing
        $this->playlist->update([
            'status' => 'processing',
            'synced' => now(),
            'errors' => null,
        ]);

        // Get ID's of existing channels and groups, in case any get removed
        $existing_channels = Channel::where('playlist_id', $this->playlist->id)->get()
            ->select('id')
            ->pluck('id')
            ->toArray();
        $existing_groups = Group::where('playlist_id', $this->playlist->id)->get()
            ->select('id')
            ->pluck('id')
            ->toArray();

        // Surround in a try/catch block to catch any exceptions
        try {
            // Keep track of new channels and groups
            $new_channels = [];
            $new_groups = [];

            $playlistId = $this->playlist->id;

            $parser = new M3UContentParser($this->playlist->url);
            $parser->parse();

            $count = 0;
            $channels = collect([]);
            $groups = collect([]);

            // Process each row of the M3U file
            foreach ($parser->all() as $item) {
                /**
                 * @var M3UItem $item 
                 */
                $channels->push([
                    'playlist_id' => $playlistId,
                    'stream_id' => $item->getId(), // usually null/empty
                    'shift' => $item->getTvgShift(), // usually null/empty
                    'name' => $item->getTvgName(),
                    'url' => $item->getTvgUrl(),
                    'logo' => $item->getTvgLogo(),
                    'group' => $item->getGroupTitle(),
                    'lang' => $item->getLanguage(), // usually null/empty
                    'country' => $item->getCountry(), // usually null/empty
                ]);

                // Maintain a list of unique channel groups
                if (!$groups->contains('title', $item->getGroupTitle())) {
                    $groups->push([
                        'id' => null,
                        'playlist_id' => $playlistId,
                        'name' => $item->getGroupTitle()
                    ]);
                }

                // Increment the counter
                $count++;
            }

            $groups = $groups->map(function ($group) use (&$new_groups) {
                // Find/create the group
                $model = Group::firstOrCreate([
                    'playlist_id' => $group['playlist_id'],
                    'name' => $group['name'],
                ]);

                // Keep track of groups
                $new_groups[] = $model->id;

                // Return the group, with the ID
                return [
                    ...$group,
                    'id' => $model->id,
                ];
            });

            // Link the channel groups to the channels
            $channels->map(function ($channel) use ($groups, &$new_channels) {
                // Find/create the channel
                $model = Channel::firstOrCreate([
                    'playlist_id' => $channel['playlist_id'],
                    'name' => $channel['name'],
                    'group' => $channel['group'],
                ]);

                // Keep track of channels
                $new_channels[] = $model->id;

                // Update the channel
                $model->update([
                    ...$channel,
                    'group_id' => $groups->firstWhere('name', $channel['group'])['id']
                ]);
                return $channel;
            });

            // Remove orphaned channels and groups
            Channel::where('playlist_id', $playlistId)
                ->whereNotIn('id', $new_channels)
                ->delete();

            Group::where('playlist_id', $playlistId)
                ->whereNotIn('id', $new_groups)
                ->delete();

            // Update the playlist
            $this->playlist->update([
                'status' => 'completed',
                'channels' => $count,
                'synced' => now(),
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            $this->playlist->update([
                'status' => 'failed',
                'channels' => 0,
                'synced' => now(),
                'errors' => $e->getMessage(),
            ]);
            return;
        }
    }
}
