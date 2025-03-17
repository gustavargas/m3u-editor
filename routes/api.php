<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API routes
Route::group(['middleware' => ['auth:sanctum']], function () {

    // API v1
    Route::group(['prefix' => 'v1'], function () {

        // Get the authenticated user
        Route::group(['prefix' => 'user'], function () {
            Route::get('whoami', [\App\Http\Controllers\ApiController::class, 'user']);
        });

        // Sync endpoints
        Route::group(['prefix' => 'sync'], function () {
            Route::post('playlist/{playlist}/{force?}', [\App\Http\Controllers\ApiController::class, 'refreshPlaylist']);
            Route::post('epg/{epg}/{force?}', [\App\Http\Controllers\ApiController::class, 'refreshEpg']);
        });

        // Playlist routes
        Route::group(['prefix' => 'playlists'], function () {
            Route::get('/', [\App\Http\Controllers\ApiController::class, 'getPlaylists']);
            Route::post('/', [\App\Http\Controllers\ApiController::class, 'createPlaylist']);
            Route::put('/{playlist}', [\App\Http\Controllers\ApiController::class, 'updatePlaylist']);
            Route::delete('/{playlist}', [\App\Http\Controllers\ApiController::class, 'deletePlaylist']);
        });

        // Group routes
        Route::group(['prefix' => 'groups'], function () {
            Route::get('/', [\App\Http\Controllers\ApiController::class, 'getGroups']);
            Route::post('/', [\App\Http\Controllers\ApiController::class, 'createGroup']);
            Route::put('/{group}', [\App\Http\Controllers\ApiController::class, 'updateGroup']);
            Route::delete('/{group}', [\App\Http\Controllers\ApiController::class, 'deleteGroup']);
        });

        // Custom Playlist routes
        Route::group(['prefix' => 'custom-playlists'], function () {
            Route::get('/', [\App\Http\Controllers\ApiController::class, 'getCustomPlaylists']);
            Route::post('/', [\App\Http\Controllers\ApiController::class, 'createCustomPlaylist']);
            Route::put('/{customPlaylist}', [\App\Http\Controllers\ApiController::class, 'updateCustomPlaylist']);
            Route::delete('/{customPlaylist}', [\App\Http\Controllers\ApiController::class, 'deleteCustomPlaylist']);
        });

        // Channel routes
        Route::group(['prefix' => 'channels'], function () {
            Route::get('/', [\App\Http\Controllers\ApiController::class, 'getChannels']);
            Route::post('/', [\App\Http\Controllers\ApiController::class, 'createChannel']);
            Route::put('/{channel}', [\App\Http\Controllers\ApiController::class, 'updateChannel']);
            Route::delete('/{channel}', [\App\Http\Controllers\ApiController::class, 'deleteChannel']);
        });

    });

    // ...
});
