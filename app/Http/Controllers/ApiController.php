<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEpgImport;
use App\Jobs\ProcessM3uImport;
use App\Models\Epg;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Get the authenticated User.
     *
     * @param \Illuminate\Http\Request $request
     * @return string[]
     * @response array{name: "admin"}
     */
    public function user(Request $request)
    {
        return $request->user()?->only('name');
    }

    /**
     * Sync the selected Playlist.
     *
     * Use the `playlist` parameter to select the playlist to refresh.
     * You can find the playlist ID by looking at the ID column when viewing the playlist table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Playlist $playlist
     * @param bool $force If true, will force a refresh of the Playlist, ignoring any scheduling.
     *
     * @return \Illuminate\Http\JsonResponse
     * @response array{message: "Response"}
     */
    public function refreshPlaylist(Request $request, Playlist $playlist, bool $force = true)
    {
        if ($request->user()->id !== $playlist->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ])->setStatusCode(403);
        }

        // Refresh the playlist
        dispatch(new ProcessM3uImport($playlist, $force));

        return response()->json([
            'message' => "Playlist \"{$playlist->name}\" is currently being synced...",
        ]);
    }

    /**
     * Sync the selected EPG.
     *
     * Use the `epg` parameter to select the EPG to refresh.
     * You can find the EPG ID by looking at the ID column when viewing the EPG table.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Playlist $epg
     * @param bool $force If true, will force a refresh of the EPG, ignoring any scheduling.
     *
     * @return \Illuminate\Http\JsonResponse
     * @response array{message: "Response"}
     */
    public function refreshEpg(Request $request, Epg $epg, bool $force = true)
    {
        if ($request->user()->id !== $epg->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ])->setStatusCode(403);
        }

        // Refresh the EPG
        // Refresh the playlist
        dispatch(new ProcessEpgImport($epg, $force));

        return response()->json([
            'message' => "EPG \"{$epg->name}\" is currently being synced...",
        ]);
    }

    /**
     * Get all playlists
     */
    public function getPlaylists(Request $request): JsonResponse
    {
        $playlists = $request->user()->playlists()->get();
        return response()->json($playlists);
    }

    /**
     * Create new playlist
     */
    public function createPlaylist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'import_prefs' => 'array',
        ]);

        $playlist = $request->user()->playlists()->create($validated);
        return response()->json($playlist, 201);
    }

    /**
     * Update existing playlist
     */
    public function updatePlaylist(Request $request, Playlist $playlist): JsonResponse
    {
        if ($request->user()->id !== $playlist->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'url' => 'url',
            'import_prefs' => 'array',
        ]);

        $playlist->update($validated);
        return response()->json($playlist);
    }

    /**
     * Delete playlist
     */
    public function deletePlaylist(Request $request, Playlist $playlist): JsonResponse
    {
        if ($request->user()->id !== $playlist->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $playlist->delete();
        return response()->json(['message' => 'Playlist deleted']);
    }

    /**
     * Get all groups
     */
    public function getGroups(Request $request): JsonResponse
    {
        $groups = $request->user()->groups()->with('playlist')->get();
        return response()->json($groups);
    }

    /**
     * Create new group
     */
    public function createGroup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'playlist_id' => 'required|exists:playlists,id',
        ]);

        $group = $request->user()->groups()->create($validated);
        return response()->json($group, 201);
    }

    /**
     * Update group
     */
    public function updateGroup(Request $request, Group $group): JsonResponse
    {
        if ($request->user()->id !== $group->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'playlist_id' => 'exists:playlists,id',
        ]);

        $group->update($validated);
        return response()->json($group);
    }

    /**
     * Delete group
     */
    public function deleteGroup(Request $request, Group $group): JsonResponse
    {
        if ($request->user()->id !== $group->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $group->delete();
        return response()->json(['message' => 'Group deleted']);
    }

    /**
     * Get custom playlists
     */
    public function getCustomPlaylists(Request $request): JsonResponse
    {
        $customPlaylists = $request->user()->customPlaylists()->with('channels')->get();
        return response()->json($customPlaylists);
    }

    /**
     * Create custom playlist
     */
    public function createCustomPlaylist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channels' => 'array',
            'channels.*' => 'exists:channels,id',
        ]);

        $customPlaylist = $request->user()->customPlaylists()->create([
            'name' => $validated['name']
        ]);

        if (isset($validated['channels'])) {
            $customPlaylist->channels()->attach($validated['channels']);
        }

        return response()->json($customPlaylist, 201);
    }

    /**
     * Update custom playlist
     */
    public function updateCustomPlaylist(Request $request, CustomPlaylist $customPlaylist): JsonResponse
    {
        if ($request->user()->id !== $customPlaylist->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'channels' => 'array',
            'channels.*' => 'exists:channels,id',
        ]);

        $customPlaylist->update([
            'name' => $validated['name'] ?? $customPlaylist->name
        ]);

        if (isset($validated['channels'])) {
            $customPlaylist->channels()->sync($validated['channels']);
        }

        return response()->json($customPlaylist);
    }

    /**
     * Delete custom playlist
     */
    public function deleteCustomPlaylist(Request $request, CustomPlaylist $customPlaylist): JsonResponse
    {
        if ($request->user()->id !== $customPlaylist->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customPlaylist->delete();
        return response()->json(['message' => 'Custom playlist deleted']);
    }

    /**
     * Get channels
     */
    public function getChannels(Request $request): JsonResponse
    {
        $channels = $request->user()->channels()->with(['playlist', 'group'])->get();
        return response()->json($channels);
    }

    /**
     * Create new channel
     */
    public function createChannel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'playlist_id' => 'required|exists:playlists,id',
            'group_id' => 'exists:groups,id',
            'enabled' => 'boolean',
            'shift' => 'integer',
        ]);

        $channel = $request->user()->channels()->create($validated);
        return response()->json($channel, 201);
    }

    /**
     * Update channel
     */
    public function updateChannel(Request $request, Channel $channel): JsonResponse
    {
        if ($request->user()->id !== $channel->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'url' => 'url',
            'playlist_id' => 'exists:playlists,id',
            'group_id' => 'exists:groups,id',
            'enabled' => 'boolean',
            'shift' => 'integer',
        ]);

        $channel->update($validated);
        return response()->json($channel);
    }

    /**
     * Delete channel
     */
    public function deleteChannel(Request $request, Channel $channel): JsonResponse
    {
        if ($request->user()->id !== $channel->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $channel->delete();
        return response()->json(['message' => 'Channel deleted']);
    }
}
