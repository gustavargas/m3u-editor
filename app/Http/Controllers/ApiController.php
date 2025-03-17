<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEpgImport;
use App\Jobs\ProcessM3uImport;
use App\Models\Epg;
use App\Models\Playlist;
use App\Models\MergedPlaylist;
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
     * Get all playlists.
     * 
     * Returns a list of all playlists belonging to the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, url: string, import_prefs: array}[]
     */
    public function getPlaylists(Request $request): JsonResponse
    {
        $playlists = $request->user()->playlists()->get();
        return response()->json($playlists);
    }

    /**
     * Create new playlist.
     * 
     * Creates a new playlist for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response 201 array{id: integer, name: string, url: string, import_prefs: array}
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
     * Update existing playlist.
     * 
     * Updates an existing playlist. Only the owner can update the playlist.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Playlist $playlist
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, url: string, import_prefs: array}
     * @response 403 array{message: "Unauthorized"}
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
     * Delete playlist.
     * 
     * Deletes an existing playlist. Only the owner can delete the playlist.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Playlist $playlist
     * @return \Illuminate\Http\JsonResponse
     * @response array{message: "Playlist deleted"}
     * @response 403 array{message: "Unauthorized"}
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
     * Get all groups.
     * 
     * Returns a list of all groups belonging to the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, playlist_id: integer, playlist: array{id: integer, name: string}}[]
     */
    public function getGroups(Request $request): JsonResponse
    {
        $groups = $request->user()->groups()->with('playlist')->get();
        return response()->json($groups);
    }

    /**
     * Create new group.
     * 
     * Creates a new group for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response 201 array{id: integer, name: string, playlist_id: integer}
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
     * Update group.
     * 
     * Updates an existing group. Only the owner can update the group.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Group $group
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, playlist_id: integer}
     * @response 403 array{message: "Unauthorized"}
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
     * Delete group.
     * 
     * Deletes an existing group. Only the owner can delete the group.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Group $group
     * @return \Illuminate\Http\JsonResponse
     * @response array{message: "Group deleted"}
     * @response 403 array{message: "Unauthorized"}
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
     * Get custom playlists.
     * 
     * Returns a list of all custom playlists belonging to the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, channels: array{id: integer, name: string}[]}[]
     */
    public function getCustomPlaylists(Request $request): JsonResponse
    {
        $customPlaylists = $request->user()->customPlaylists()->with('channels')->get();
        return response()->json($customPlaylists);
    }

    /**
     * Create custom playlist.
     * 
     * Creates a new custom playlist for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response 201 array{id: integer, name: string, channels: array{id: integer, name: string}[]}
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
     * Update custom playlist.
     * 
     * Updates an existing custom playlist. Only the owner can update the custom playlist.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\CustomPlaylist $customPlaylist
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, channels: array{id: integer, name: string}[]}
     * @response 403 array{message: "Unauthorized"}
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
     * Delete custom playlist.
     * 
     * Deletes an existing custom playlist. Only the owner can delete the custom playlist.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\CustomPlaylist $customPlaylist
     * @return \Illuminate\Http\JsonResponse
     * @response array{message: "Custom playlist deleted"}
     * @response 403 array{message: "Unauthorized"}
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
     * Get channels.
     * 
     * Returns a list of all channels belonging to the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, url: string, playlist_id: integer, group_id: integer|null, enabled: boolean, shift: integer, playlist: array{id: integer, name: string}, group: array{id: integer, name: string}|null}[]
     */
    public function getChannels(Request $request): JsonResponse
    {
        $channels = $request->user()->channels()->with(['playlist', 'group'])->get();
        return response()->json($channels);
    }

    /**
     * Create new channel.
     * 
     * Creates a new channel for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response 201 array{id: integer, name: string, url: string, playlist_id: integer, group_id: integer|null, enabled: boolean, shift: integer}
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
     * Update channel.
     * 
     * Updates an existing channel. Only the owner can update the channel.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Channel $channel
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, url: string, playlist_id: integer, group_id: integer|null, enabled: boolean, shift: integer}
     * @response 403 array{message: "Unauthorized"}
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
     * Delete channel.
     * 
     * Deletes an existing channel. Only the owner can delete the channel.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Channel $channel
     * @return \Illuminate\Http\JsonResponse
     * @response array{message: "Channel deleted"}
     * @response 403 array{message: "Unauthorized"}
     */
    public function deleteChannel(Request $request, Channel $channel): JsonResponse
    {
        if ($request->user()->id !== $channel->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $channel->delete();
        return response()->json(['message' => 'Channel deleted']);
    }
    /**
     * Get merged playlists.
     * 
     * Returns a list of all merged playlists belonging to the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, playlists: array{id: integer, name: string}[], channels: array{id: integer, name: string}[]}[]
     */
    public function getMergedPlaylists(Request $request): JsonResponse
    {
        $mergedPlaylists = $request->user()->mergedPlaylists()
            ->with(['playlists', 'channels'])
            ->get();
        return response()->json($mergedPlaylists);
    }

    /**
     * Create merged playlist.
     * 
     * Creates a new merged playlist for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @response 201 array{id: integer, name: string, playlists: array{id: integer, name: string}[]}
     */
    public function createMergedPlaylist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'playlists' => 'required|array',
            'playlists.*' => 'exists:playlists,id'
        ]);

        $mergedPlaylist = $request->user()->mergedPlaylists()->create([
            'name' => $validated['name']
        ]);

        $mergedPlaylist->playlists()->attach($validated['playlists']);

        return response()->json($mergedPlaylist->load('playlists'), 201);
    }

    /**
     * Update merged playlist.
     * 
     * Updates an existing merged playlist. Only the owner can update the merged playlist.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\MergedPlaylist $mergedPlaylist
     * @return \Illuminate\Http\JsonResponse
     * @response array{id: integer, name: string, playlists: array{id: integer, name: string}[]}
     * @response 403 array{message: "Unauthorized"}
     */
    public function updateMergedPlaylist(Request $request, MergedPlaylist $mergedPlaylist): JsonResponse
    {
        if ($request->user()->id !== $mergedPlaylist->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'playlists' => 'array',
            'playlists.*' => 'exists:playlists,id'
        ]);

        if (isset($validated['name'])) {
            $mergedPlaylist->update(['name' => $validated['name']]);
        }

        if (isset($validated['playlists'])) {
            $mergedPlaylist->playlists()->sync($validated['playlists']);
        }

        return response()->json($mergedPlaylist->load('playlists'));
    }

    /**
     * Delete merged playlist.
     * 
     * Deletes an existing merged playlist. Only the owner can delete the merged playlist.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\MergedPlaylist $mergedPlaylist
     * @return \Illuminate\Http\JsonResponse
     * @response array{message: "Merged playlist deleted"}
     * @response 403 array{message: "Unauthorized"}
     */
    public function deleteMergedPlaylist(Request $request, MergedPlaylist $mergedPlaylist): JsonResponse
    {
        if ($request->user()->id !== $mergedPlaylist->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $mergedPlaylist->delete();
        return response()->json(['message' => 'Merged playlist deleted']);
    }
}
