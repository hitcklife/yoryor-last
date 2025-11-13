<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserProfile extends Component
{
    public $userId;
    public $user;

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->loadUser();
    }

    public function loadUser()
    {
        try {
            $this->user = User::with([
                'profile.country', 
                'photos', 
                'culturalProfile', 
                'familyPreference', 
                'careerProfile',
                'physicalProfile',
                'locationPreference',
                'roles'
            ])->find($this->userId);

            if (!$this->user) {
                abort(404, 'User not found');
            }
        } catch (\Exception $e) {
            Log::error('Failed to load user profile: ' . $e->getMessage());
            // Fallback to basic user data
            $this->user = User::with(['profile.country', 'photos'])->find($this->userId);
            
            if (!$this->user) {
                abort(404, 'User not found');
            }
        }
    }

    public function toggleUserStatus()
    {
        if ($this->user) {
            $this->user->update([
                'disabled_at' => $this->user->disabled_at ? null : now()
            ]);
            
            $this->dispatch('user-updated', [
                'message' => $this->user->disabled_at ? 'User disabled successfully' : 'User enabled successfully',
                'type' => 'success'
            ]);
            
            // Refresh user data
            $this->loadUser();
        }
    }

    public function deleteUser()
    {
        if ($this->user) {
            $this->user->delete();
            
            $this->dispatch('user-updated', [
                'message' => 'User deleted successfully',
                'type' => 'success'
            ]);
            
            // Redirect back to users list
            return redirect()->route('admin.users');
        }
    }

    public function render()
    {
        return view('livewire.admin.user-profile')
            ->layout('components.layouts.admin', [
                'title' => $this->user ? 'User Profile - ' . ($this->user->profile?->first_name . ' ' . $this->user->profile?->last_name ?: 'User #' . $this->user->id) : 'User Profile'
            ]);
    }
}
