<?php

namespace Rjcodes\Rjcms\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rjcodes\Rjcms\Database\Factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The auth guard this model's roles/permissions belong to.
     *
     * Pinned to "web" so Spatie can always resolve the guard, even when the
     * host app's `auth.providers.users.model` points at its own User model
     * (the common case). Without this, syncRoles() throws GuardDoesNotMatch.
     */
    protected $guard_name = 'web';

    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }

    /**
     * The public URL of the user's avatar image, or null when none is set.
     */
    public function avatarUrl(): ?string
    {
        return $this->avatar
            ? Media::find($this->avatar)?->url
            : null;
    }

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
        ];
    }
}
