<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
        ];
    }

    /**
     * Get roles for current team
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Get permissions through roles for current team
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
            'role_id',
            'permission_id'
        )->through('roles');
    }

    /**
     * Check if user has a specific role in current team
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles()
                ->where('team_id', $this->current_team_id)
                ->where('name', $role)
                ->exists();
        }

        return $this->roles()
            ->where('team_id', $this->current_team_id)
            ->whereIn('name', (array) $role)
            ->exists();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission): bool
    {
        if ($this->hasRole('admin')) {
            return true; // Admins have all permissions
        }

        return $this->permissions()
            ->where('team_id', $this->current_team_id)
            ->where('name', $permission)
            ->exists();
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission($permissions): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        return $this->permissions()
            ->where('team_id', $this->current_team_id)
            ->whereIn('name', (array) $permissions)
            ->exists();
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions($permissions): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        $count = $this->permissions()
            ->where('team_id', $this->current_team_id)
            ->whereIn('name', (array) $permissions)
            ->count();

        return $count === count((array) $permissions);
    }

    /**
     * Get all roles for user
     */
    public function getAllRoles()
    {
        return $this->roles()
            ->where('team_id', $this->current_team_id)
            ->get();
    }

    /**
     * Assign a role to user
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('team_id', $this->current_team_id)
                ->where('name', $role)
                ->first();
        }

        if (!$role) {
            return false;
        }

        return $this->roles()->sync($role->id, false);
    }

    /**
     * Remove a role from user
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('team_id', $this->current_team_id)
                ->where('name', $role)
                ->first();
        }

        if (!$role) {
            return false;
        }

        return $this->roles()->detach($role->id);
    }
}
