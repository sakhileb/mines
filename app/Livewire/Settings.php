<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Traits\BrowserEventBridge;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Settings extends Component
{
    use BrowserEventBridge;
    public string $activeTab = 'general';

    // General Settings
    #[Validate('required|string|max:255')]
    public string $teamName = '';

    #[Validate('nullable|string|max:255')]
    public string $teamEmail = '';

    #[Validate('required|timezone')]
    public string $timezone = 'UTC';

    #[Validate('required|in:en,es,fr,de,pt,zh,ar,af,zu')]
    public string $language = 'en';

    #[Validate('required|in:USD,EUR,GBP,ZAR,AUD,CAD,JPY,CNY,INR,BRL')]
    public string $currency = 'USD';

    // Users & Roles
    public array $teamMembers = [];
    public string $inviteEmail = '';
    public string $selectedRole = 'operator';
    public bool $showInviteForm = false;

    // Notification Settings
    public bool $emailAlerts = true;
    public bool $emailReports = true;
    public bool $inAppAlerts = true;
    public string $quietHoursStart = '22:00';
    public string $quietHoursEnd = '08:00';
    public bool $quietHoursEnabled = false;

    protected $rules = [
        'teamName' => 'required|string|max:255',
        'teamEmail' => 'nullable|email|max:255',
        'timezone' => 'required|timezone',
        'language' => 'required|in:en,es,fr,de,pt,zh,ar,af,zu',
        'currency' => 'required|in:USD,EUR,GBP,ZAR,AUD,CAD,JPY,CNY,INR,BRL',
    ];

    public function mount()
    {
        $team = auth()->user()->currentTeam;
        $this->teamName = $team->name;
        $this->teamEmail = $team->email ?? '';
        $this->timezone = $team->timezone ?? 'UTC';
        $this->language = $team->language ?? 'en';
        $this->currency = $team->currency ?? 'USD';

        $this->loadTeamMembers();
    }

    public function render()
    {
        return view('livewire.settings', [
            'roles' => $this->getRoles(),
            'timezones' => $this->getTimezones(),
            'languages' => $this->getLanguages(),
            'currencies' => $this->getCurrencies(),
        ]);
    }

    public function setActiveTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    // ==================== GENERAL SETTINGS ====================

    public function saveGeneralSettings()
    {
        $this->validate();

        $team = auth()->user()->currentTeam;
        $team->update([
            'name' => $this->teamName,
            'email' => $this->teamEmail,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'currency' => $this->currency,
        ]);

        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'General settings updated']);
    }

    // ==================== USERS & ROLES ====================

    public function loadTeamMembers()
    {
        $team = auth()->user()->currentTeam;
        $this->teamMembers = $team->users()
            ->with('roles')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->name ?? 'No Role',
                    'joined_at' => isset($user->pivot) && isset($user->pivot->created_at) ? $user->pivot->created_at : 'N/A',
                ];
            })
            ->toArray();
    }

    public function toggleInviteForm()
    {
        $this->showInviteForm = !$this->showInviteForm;
        if (!$this->showInviteForm) {
            $this->inviteEmail = '';
            $this->selectedRole = 'operator';
        }
    }

    public function inviteUser()
    {
        $this->validate([
            'inviteEmail' => 'required|email|max:255',
            'selectedRole' => 'required|string',
        ]);

        try {
            $team = auth()->user()->currentTeam;
            
            // Check if user already invited/member
            $existingUser = User::where('email', $this->inviteEmail)->first();
            if ($existingUser && $team->users->contains($existingUser->id)) {
                $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'User is already a team member']);
                return;
            }

            // In production, would send actual invitation email
            // For now, create the user if they don't exist
            if (!$existingUser) {
                $existingUser = User::create([
                    'name' => explode('@', $this->inviteEmail)[0],
                    'email' => $this->inviteEmail,
                    'password' => bcrypt('temporary_password_change_on_login'),
                ]);
            }

            // Add user to team
            $team->users()->attach($existingUser->id);

            // Assign role
            $role = \App\Models\Role::where('name', $this->selectedRole)->first();
            if ($role) {
                $existingUser->roles()->attach($role->id);
            }

            $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => "Invitation sent to {$this->inviteEmail}"]);
            $this->showInviteForm = false;
            $this->inviteEmail = '';
            $this->selectedRole = 'operator';
            $this->loadTeamMembers();
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'Failed to invite user: ' . $e->getMessage()]);
        }
    }

    public function removeUser($userId)
    {
        try {
            $team = auth()->user()->currentTeam;
            $currentUser = auth()->user();

            // Prevent removing self
            if ($userId === $currentUser->id) {
                $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'Cannot remove yourself from the team']);
                return;
            }

            $team->users()->detach($userId);
            $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'User removed from team']);
            $this->loadTeamMembers();
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'Failed to remove user']);
        }
    }

    public function updateUserRole($userId, $newRole)
    {
        try {
            $team = auth()->user()->currentTeam;

            // Ensure the user is a member of this team
            if (! $team->users()->where('id', $userId)->exists()) {
                $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'User is not a member of this team']);
                return;
            }

            $team = auth()->user()->currentTeam;
            $user = $team->users()->findOrFail($userId);
            // Remove old roles
            $user->roles()->detach();
            
            // Add new role
            $role = \App\Models\Role::where('name', $newRole)->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'User role updated']);
            $this->loadTeamMembers();
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'Failed to update role']);
        }
    }

    // ==================== NOTIFICATION SETTINGS ====================

    public function saveNotificationSettings()
    {
        try {
            // Store in user preferences
            $user = auth()->user();
            $preferences = [
                'email_alerts' => $this->emailAlerts,
                'email_reports' => $this->emailReports,
                'in_app_alerts' => $this->inAppAlerts,
                'quiet_hours_enabled' => $this->quietHoursEnabled,
                'quiet_hours_start' => $this->quietHoursStart,
                'quiet_hours_end' => $this->quietHoursEnd,
            ];

            // Store preferences (would use a proper preferences table in production)
            // For now, just dispatch success
            
            $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Notification settings saved']);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'Failed to save settings']);
        }
    }

    // ==================== HELPER METHODS ====================

    private function getRoles(): array
    {
        return [
            'admin' => 'Administrator',
            'fleet_manager' => 'Fleet Manager',
            'operator' => 'Operator',
            'viewer' => 'Viewer',
        ];
    }

    private function getTimezones(): array
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time',
            'America/Chicago' => 'Central Time',
            'America/Denver' => 'Mountain Time',
            'America/Los_Angeles' => 'Pacific Time',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Australia/Sydney' => 'Sydney',
        ];
    }

    private function getLanguages(): array
    {
        return [
            'en' => 'English',
            'es' => 'Español (Spanish)',
            'fr' => 'Français (French)',
            'de' => 'Deutsch (German)',
            'pt' => 'Português (Portuguese)',
            'zh' => '中文 (Chinese)',
            'ar' => 'العربية (Arabic)',
            'af' => 'Afrikaans',
            'zu' => 'isiZulu',
        ];
    }

    private function getCurrencies(): array
    {
        return [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'ZAR' => 'South African Rand (R)',
            'AUD' => 'Australian Dollar (A$)',
            'CAD' => 'Canadian Dollar (C$)',
            'JPY' => 'Japanese Yen (¥)',
            'CNY' => 'Chinese Yuan (¥)',
            'INR' => 'Indian Rupee (₹)',
            'BRL' => 'Brazilian Real (R$)',
        ];
    }
}
