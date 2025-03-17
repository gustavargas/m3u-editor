<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // 'avatar_url' => 'array'
        ];
    }

    /**
     * Who can access the Filament admin panel in production?
     * Allow all users by default.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
        // return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    /**
     * Users playlists.
     */
    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

        /**
         * User's Groups.
         * One-to-many relationship with the Group model.
         */
        public function groups()
        {
            return $this->hasMany(Group::class);
        }

        /**
         * User's Custom Playlists.
         * One-to-many relationship with the CustomPlaylist model.
         */
        public function customPlaylists()
        {
            return $this->hasMany(CustomPlaylist::class);
        }

        /**
         * User's Merge Playlists.
         * One-to-many relationship with the MergePlaylist model.
         */
        public function mergePlaylists()
        {
            return $this->hasMany(MergePlaylist::class);
        }

        /**
         * User's Channels.
         * One-to-many relationship with the Channel model.
         */
        public function channels()
        {
            return $this->hasMany(Channel::class);
        }

    /**
     * Users epgs.
     */
    public function epgs()
    {
        return $this->hasMany(Epg::class);
    }
}
