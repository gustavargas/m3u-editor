<?php

namespace App\Jobs;

use App\Enums\PlaylistStatus;
use App\Models\Channel;
use App\Models\Group;
use App\Models\Playlist;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class ProcessChannelImport implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $count,
        public Collection $groups,
        public Collection $channels
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }

        try {
            // Keep track of new channels and groups
            $new_channels = [];

            $groups = $this->groups;

            // Link the channel groups to the channels
            $this->channels->map(function ($channel) use ($groups, &$new_channels) {
                // Find/create the channel
                $model = Channel::firstOrCreate([
                    'playlist_id' => $channel['playlist_id'],
                    'user_id' => $channel['user_id'],
                    'name' => $channel['name'],
                    'group' => $channel['group'],
                ]);

                // Keep track of channels
                // $new_channels[] = $model->id;

                // Update the channel
                $model->update([
                    ...$channel,
                    'group_id' => $groups->firstWhere('name', $channel['group'])['id']
                ]);
                return $channel;
            });

            // // Remove orphaned channels and groups
            // Channel::where('playlist_id', $playlistId)
            //     ->whereNotIn('id', $new_channels)
            //     ->delete();
        } catch (\Exception $e) {
            // Log the exception
            logger()->error($e->getMessage());
        }
        return;
    }
}
