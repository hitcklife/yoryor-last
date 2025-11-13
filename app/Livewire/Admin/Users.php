<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Users extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $genderFilter = '';
    public $countryFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'genderFilter' => ['except' => ''],
        'countryFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingGenderFilter()
    {
        $this->resetPage();
    }

    public function updatingCountryFilter()
    {
        $this->resetPage();
    }

    public function sortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->genderFilter = '';
        $this->countryFilter = '';
        $this->resetPage();
    }

    public function viewUser($userId)
    {
        return redirect()->route('admin.user.profile', $userId);
    }


    public function toggleUserStatus($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->update([
                'disabled_at' => $user->disabled_at ? null : now()
            ]);
            
            $this->dispatch('user-updated', [
                'message' => $user->disabled_at ? 'User disabled successfully' : 'User enabled successfully',
                'type' => 'success'
            ]);
        }
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->delete();
            $this->dispatch('user-updated', [
                'message' => 'User deleted successfully',
                'type' => 'success'
            ]);
        }
    }

    public function getUsers()
    {
        try {
            $query = User::query();
            
            // Add relationships safely
            $query->with(['profile.country', 'photos']);
            
            // Add search conditions
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                    
                    // Only add profile search if profile relationship exists
                    try {
                        $q->orWhereHas('profile', function ($profile) {
                            $profile->where('first_name', 'like', '%' . $this->search . '%')
                                    ->orWhere('last_name', 'like', '%' . $this->search . '%');
                        });
                    } catch (\Exception $e) {
                        Log::warning('Profile search failed: ' . $e->getMessage());
                    }
                });
            }
            
            // Add status filter
            if ($this->statusFilter) {
                switch ($this->statusFilter) {
                    case 'active':
                        $query->whereNull('disabled_at')
                              ->where('registration_completed', true);
                        break;
                    case 'disabled':
                        $query->whereNotNull('disabled_at');
                        break;
                    case 'incomplete':
                        $query->where('registration_completed', false);
                        break;
                    case 'verified':
                        $query->where(function ($q) {
                            $q->whereNotNull('email_verified_at')
                              ->orWhereNotNull('phone_verified_at');
                        });
                        break;
                    case 'unverified':
                        $query->whereNull('email_verified_at')
                              ->whereNull('phone_verified_at');
                        break;
                }
            }
            
            // Add gender filter
            if ($this->genderFilter) {
                try {
                    $query->whereHas('profile', function ($profile) {
                        $profile->where('gender', $this->genderFilter);
                    });
                } catch (\Exception $e) {
                    Log::warning('Gender filter failed: ' . $e->getMessage());
                }
            }
            
            // Add country filter
            if ($this->countryFilter) {
                try {
                    $query->whereHas('profile', function ($profile) {
                        // Search both JSON format and plain text
                        $profile->where('country', 'like', '%"name":"' . $this->countryFilter . '"%')
                               ->orWhere('country', $this->countryFilter);
                    });
                } catch (\Exception $e) {
                    Log::warning('Country filter failed: ' . $e->getMessage());
                }
            }
            
            return $query->orderBy($this->sortBy, $this->sortDirection)
                         ->paginate($this->perPage);
                         
        } catch (\Exception $e) {
            Log::error('getUsers failed: ' . $e->getMessage());
            // Return empty paginated collection on error
            return User::query()->whereRaw('1 = 0')->paginate($this->perPage);
        }
    }

    public function render()
    {
        try {
            $users = $this->getUsers();
            
            // Get filter options safely
            $countries = collect();
            $genders = collect();
            
            try {
                $countries = Profile::whereNotNull('country')
                    ->get()
                    ->map(function ($profile) {
                        return $profile->country_name;
                    })
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
            } catch (\Exception $e) {
                // Ignore if Profile table issues
            }
                
            try {
                $genders = Profile::whereNotNull('gender')
                    ->distinct()
                    ->pluck('gender');
            } catch (\Exception $e) {
                // Provide default genders if table issues
                $genders = collect(['male', 'female', 'non-binary', 'other']);
            }

            return view('livewire.admin.users', [
                'users' => $users,
                'countries' => $countries,
                'genders' => $genders
            ])->layout('components.layouts.admin', ['title' => 'User Management']);
        } catch (\Exception $e) {
            // Return basic error view
            return view('livewire.admin.users-error', [
                'error' => $e->getMessage()
            ])->layout('components.layouts.admin', ['title' => 'User Management - Error']);
        }
    }
}
