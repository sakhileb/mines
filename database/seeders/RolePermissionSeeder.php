<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // Dashboard permissions
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'group' => 'dashboard', 'description' => 'View main dashboard and metrics'],
            
            // Machine permissions
            ['name' => 'view_machines', 'display_name' => 'View Machines', 'group' => 'machines', 'description' => 'View fleet machines'],
            ['name' => 'create_machines', 'display_name' => 'Create Machines', 'group' => 'machines', 'description' => 'Add new machines to fleet'],
            ['name' => 'update_machines', 'display_name' => 'Update Machines', 'group' => 'machines', 'description' => 'Update machine information'],
            ['name' => 'delete_machines', 'display_name' => 'Delete Machines', 'group' => 'machines', 'description' => 'Remove machines from fleet'],
            ['name' => 'track_machines', 'display_name' => 'Track Machines', 'group' => 'machines', 'description' => 'Track real-time machine location'],
            ['name' => 'view_metrics', 'display_name' => 'View Metrics', 'group' => 'machines', 'description' => 'View machine sensor metrics'],
            
            // Map permissions
            ['name' => 'view_live_map', 'display_name' => 'View Live Map', 'group' => 'map', 'description' => 'View real-time machine locations'],
            
            // Geofence permissions
            ['name' => 'view_geofences', 'display_name' => 'View Geofences', 'group' => 'geofences', 'description' => 'View pit/geofence areas'],
            ['name' => 'create_geofences', 'display_name' => 'Create Geofences', 'group' => 'geofences', 'description' => 'Create new pit areas'],
            ['name' => 'update_geofences', 'display_name' => 'Update Geofences', 'group' => 'geofences', 'description' => 'Update pit information'],
            ['name' => 'delete_geofences', 'display_name' => 'Delete Geofences', 'group' => 'geofences', 'description' => 'Remove pit areas'],
            
            // Report permissions
            ['name' => 'view_reports', 'display_name' => 'View Reports', 'group' => 'reports', 'description' => 'View generated reports'],
            ['name' => 'create_reports', 'display_name' => 'Create Reports', 'group' => 'reports', 'description' => 'Generate new reports'],
            ['name' => 'update_reports', 'display_name' => 'Update Reports', 'group' => 'reports', 'description' => 'Update report settings'],
            ['name' => 'delete_reports', 'display_name' => 'Delete Reports', 'group' => 'reports', 'description' => 'Remove reports'],
            
            // Integration permissions
            ['name' => 'view_integrations', 'display_name' => 'View Integrations', 'group' => 'integrations', 'description' => 'View API integrations'],
            ['name' => 'manage_integrations', 'display_name' => 'Manage Integrations', 'group' => 'integrations', 'description' => 'Add/edit API integrations'],
            ['name' => 'sync_integrations', 'display_name' => 'Sync Integrations', 'group' => 'integrations', 'description' => 'Trigger integration data sync'],
            
            // Alert permissions
            ['name' => 'view_alerts', 'display_name' => 'View Alerts', 'group' => 'alerts', 'description' => 'View system alerts'],
            ['name' => 'create_alerts', 'display_name' => 'Create Alerts', 'group' => 'alerts', 'description' => 'Create manual alerts'],
            ['name' => 'update_alerts', 'display_name' => 'Update Alerts', 'group' => 'alerts', 'description' => 'Update alert settings'],
            ['name' => 'delete_alerts', 'display_name' => 'Delete Alerts', 'group' => 'alerts', 'description' => 'Remove alerts'],
            ['name' => 'acknowledge_alerts', 'display_name' => 'Acknowledge Alerts', 'group' => 'alerts', 'description' => 'Mark alerts as acknowledged'],
            ['name' => 'resolve_alerts', 'display_name' => 'Resolve Alerts', 'group' => 'alerts', 'description' => 'Mark alerts as resolved'],
            
            // Settings permissions
            ['name' => 'view_settings', 'display_name' => 'View Settings', 'group' => 'settings', 'description' => 'View team settings'],
            ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'group' => 'settings', 'description' => 'Modify team settings'],
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'group' => 'settings', 'description' => 'Add/remove team members'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'group' => 'settings', 'description' => 'Assign roles to users'],
        ];

        // Define roles and their permissions
        $roles = [
            'admin' => [
                'display_name' => 'Administrator',
                'description' => 'Full system access',
                'permissions' => array_column($permissions, 'name'), // All permissions
            ],
            'fleet_manager' => [
                'display_name' => 'Fleet Manager',
                'description' => 'Can manage machines and view reports',
                'permissions' => [
                    'view_dashboard',
                    'view_machines', 'create_machines', 'update_machines', 'track_machines', 'view_metrics',
                    'view_live_map',
                    'view_geofences', 'create_geofences', 'update_geofences',
                    'view_reports', 'create_reports',
                    'view_integrations',
                    'view_alerts', 'acknowledge_alerts', 'resolve_alerts',
                    'view_settings',
                ],
            ],
            'operator' => [
                'display_name' => 'Operator',
                'description' => 'Can view machines and maps',
                'permissions' => [
                    'view_dashboard',
                    'view_machines', 'track_machines', 'view_metrics',
                    'view_live_map',
                    'view_geofences',
                    'view_alerts', 'acknowledge_alerts',
                ],
            ],
            'viewer' => [
                'display_name' => 'Viewer',
                'description' => 'Read-only access',
                'permissions' => [
                    'view_dashboard',
                    'view_machines', 'view_metrics',
                    'view_live_map',
                    'view_geofences',
                    'view_reports',
                    'view_alerts',
                ],
            ],
        ];

        // Get all teams (that exist from Jetstream setup)
        $teams = \App\Models\Team::all();

        if ($teams->isEmpty()) {
            $this->command->warn('No teams found. Create a team first using the application.');
            return;
        }

        foreach ($teams as $team) {
            // Create permissions for this team
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'team_id' => $team->id,
                    'name' => $permission['name'],
                ], [
                    'display_name' => $permission['display_name'],
                    'group' => $permission['group'],
                    'description' => $permission['description'],
                ]);
            }

            // Create roles for this team.
            // NOTE: roles.name is globally unique in current schema, so a role name
            // can only be owned by one team.
            foreach ($roles as $roleKey => $roleData) {
                $role = Role::firstOrCreate([
                    'name' => $roleKey,
                ], [
                    'team_id' => $team->id,
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                ]);

                // If this role name already belongs to another team, skip safely.
                if ((int) $role->team_id !== (int) $team->id) {
                    continue;
                }

                // Attach permissions to role
                $permissionIds = Permission::where('team_id', $team->id)
                    ->whereIn('name', $roleData['permissions'])
                    ->pluck('id')
                    ->toArray();

                $role->permissions()->sync($permissionIds);
            }

            $this->command->info("Roles and permissions processed for team: {$team->name}");
        }
    }
}
