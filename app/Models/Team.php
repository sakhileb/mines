<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

/**
 * Team Model
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $personal_team
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 */
class Team extends JetstreamTeam
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    /**
     * Get the roles for this team.
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get the owner of the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User>
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the permissions for this team.
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Get the machines for this team.
     */
    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    /**
     * Get the geofences for this team.
     */
    public function geofences()
    {
        return $this->hasMany(Geofence::class);
    }

    /**
     * Get the alerts for this team.
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Get the integrations for this team.
     */
    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }
}
