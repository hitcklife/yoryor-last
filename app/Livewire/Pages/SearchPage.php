<?php

namespace App\Livewire\Pages;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class SearchPage extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $filters = [
        'age_min' => 18,
        'age_max' => 65,
        'distance' => 50,
        'gender' => '',
        'interests' => [],
        'education' => '',
        'profession' => '',
        'height_min' => '',
        'height_max' => '',
        'relationship_goals' => '',
        'has_photos' => false,
        'verified_only' => false,
        'online_only' => false
    ];
    public $searchHistory = [];
    public $savedSearches = [];
    public $suggestions = [];
    public $showFilters = false;
    public $totalResults = 0;
    public $sortBy = 'relevance';

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'filters' => ['except' => []],
        'sortBy' => ['except' => 'relevance'],
    ];

    public function mount()
    {
        $this->loadSearchHistory();
        $this->loadSavedSearches();
        $this->performSearch();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->loadSuggestions();
        $this->performSearch();
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->performSearch();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
        $this->performSearch();
    }

    public function loadSearchHistory()
    {
        // TODO: Load from actual search history model
        $this->searchHistory = [
            'Blonde hair',
            'Engineers',
            'Near me',
            'Dog lovers',
            'Fitness enthusiasts'
        ];
    }

    public function loadSavedSearches()
    {
        // TODO: Load from actual saved searches model
        $this->savedSearches = [
            [
                'id' => 1,
                'name' => 'My Ideal Match',
                'filters' => $this->filters,
                'created_at' => now()->subDays(5)
            ],
            [
                'id' => 2,
                'name' => 'Local Professionals',
                'filters' => array_merge($this->filters, ['profession' => 'engineer', 'distance' => 25]),
                'created_at' => now()->subDays(10)
            ]
        ];
    }

    public function loadSuggestions()
    {
        if (strlen($this->searchTerm) < 2) {
            $this->suggestions = [];
            return;
        }

        // TODO: Implement actual search suggestions
        $this->suggestions = [
            'Blonde hair',
            'Blue eyes',
            'Engineers',
            'Teachers',
            'Near me',
            'Dog lovers',
            'Fitness enthusiasts',
            'Art lovers',
            'Travelers'
        ];
    }

    public function performSearch()
    {
        $query = User::with(['profile', 'photos'])
            ->whereHas('profile', function($q) {
                $q->whereNotNull('profile_completed_at');
            })
            ->where('id', '!=', Auth::id());

        // Apply search term
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhereHas('profile', function($profileQuery) {
                      $profileQuery->where('first_name', 'like', '%' . $this->searchTerm . '%')
                                  ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                                  ->orWhere('bio', 'like', '%' . $this->searchTerm . '%');
                  });
            });
        }

        // Apply filters
        if (!empty($this->filters['gender'])) {
            $query->whereHas('profile', function($q) {
                $q->where('gender', $this->filters['gender']);
            });
        }

        if (!empty($this->filters['age_min']) || !empty($this->filters['age_max'])) {
            $query->whereHas('profile', function($q) {
                if (!empty($this->filters['age_min'])) {
                    $q->whereRaw('EXTRACT(YEAR FROM AGE(date_of_birth)) >= ?', [$this->filters['age_min']]);
                }
                if (!empty($this->filters['age_max'])) {
                    $q->whereRaw('EXTRACT(YEAR FROM AGE(date_of_birth)) <= ?', [$this->filters['age_max']]);
                }
            });
        }

        if (!empty($this->filters['interests'])) {
            $query->whereHas('profile', function($q) {
                foreach ($this->filters['interests'] as $interest) {
                    $q->whereJsonContains('interests', $interest);
                }
            });
        }

        if (!empty($this->filters['education'])) {
            $query->whereHas('profile', function($q) {
                $q->where('education', $this->filters['education']);
            });
        }

        if (!empty($this->filters['profession'])) {
            $query->whereHas('profile', function($q) {
                $q->where('profession', 'like', '%' . $this->filters['profession'] . '%');
            });
        }

        if ($this->filters['has_photos']) {
            $query->whereHas('photos');
        }

        if ($this->filters['verified_only']) {
            $query->whereNotNull('email_verified_at');
        }

        if ($this->filters['online_only']) {
            $query->where('last_seen_at', '>=', now()->subMinutes(5));
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'distance':
                // TODO: Implement distance sorting
                $query->orderBy('created_at', 'desc');
                break;
            default: // relevance
                $query->orderBy('created_at', 'desc');
                break;
        }

        $this->totalResults = $this->results->total();
    }

    public function selectSuggestion($suggestion)
    {
        $this->searchTerm = $suggestion;
        $this->suggestions = [];
        $this->performSearch();
    }

    public function clearSearch()
    {
        $this->searchTerm = '';
        $this->suggestions = [];
        $this->performSearch();
    }

    public function clearFilters()
    {
        $this->filters = [
            'age_min' => 18,
            'age_max' => 65,
            'distance' => 50,
            'gender' => '',
            'interests' => [],
            'education' => '',
            'profession' => '',
            'height_min' => '',
            'height_max' => '',
            'relationship_goals' => '',
            'has_photos' => false,
            'verified_only' => false,
            'online_only' => false
        ];
        $this->performSearch();
    }

    public function saveSearch($name = null)
    {
        if (!$name) {
            $name = 'Saved Search ' . now()->format('M j, Y');
        }

        // TODO: Implement actual save search functionality
        session()->flash('success', 'Search saved successfully!');
        $this->loadSavedSearches();
    }

    public function loadSavedSearch($searchId)
    {
        $savedSearch = collect($this->savedSearches)->firstWhere('id', $searchId);
        if ($savedSearch) {
            $this->filters = $savedSearch['filters'];
            $this->performSearch();
        }
    }

    public function deleteSavedSearch($searchId)
    {
        // TODO: Implement actual delete functionality
        session()->flash('success', 'Saved search deleted!');
        $this->loadSavedSearches();
    }

    public function getAvailableInterests()
    {
        return [
            'Travel', 'Music', 'Sports', 'Art', 'Food', 'Movies', 'Books',
            'Fitness', 'Dancing', 'Photography', 'Cooking', 'Gaming',
            'Nature', 'Technology', 'Fashion', 'Animals'
        ];
    }

    public function getEducationLevels()
    {
        return [
            'High School', 'Associate Degree', 'Bachelor\'s Degree',
            'Master\'s Degree', 'PhD', 'Professional Degree'
        ];
    }

    public function getRelationshipGoals()
    {
        return [
            'Casual Dating', 'Serious Relationship', 'Marriage',
            'Friendship', 'Something Casual', 'Not Sure'
        ];
    }

    public function getResultsProperty()
    {
        $query = User::with(['profile', 'photos'])
            ->whereHas('profile', function($q) {
                $q->whereNotNull('profile_completed_at');
            })
            ->where('id', '!=', Auth::id());

        // Apply search term
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhereHas('profile', function($profileQuery) {
                      $profileQuery->where('first_name', 'like', '%' . $this->searchTerm . '%')
                                  ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                                  ->orWhere('bio', 'like', '%' . $this->searchTerm . '%');
                  });
            });
        }

        // Apply filters
        if (!empty($this->filters['gender'])) {
            $query->whereHas('profile', function($q) {
                $q->where('gender', $this->filters['gender']);
            });
        }

        if (!empty($this->filters['age_min']) || !empty($this->filters['age_max'])) {
            $query->whereHas('profile', function($q) {
                if (!empty($this->filters['age_min'])) {
                    $q->whereRaw('EXTRACT(YEAR FROM AGE(date_of_birth)) >= ?', [$this->filters['age_min']]);
                }
                if (!empty($this->filters['age_max'])) {
                    $q->whereRaw('EXTRACT(YEAR FROM AGE(date_of_birth)) <= ?', [$this->filters['age_max']]);
                }
            });
        }

        if (!empty($this->filters['interests'])) {
            $query->whereHas('profile', function($q) {
                foreach ($this->filters['interests'] as $interest) {
                    $q->whereJsonContains('interests', $interest);
                }
            });
        }

        if (!empty($this->filters['education'])) {
            $query->whereHas('profile', function($q) {
                $q->where('education_level', $this->filters['education']);
            });
        }

        if (!empty($this->filters['profession'])) {
            $query->whereHas('profile', function($q) {
                $q->where('profession', 'like', '%' . $this->filters['profession'] . '%');
            });
        }

        if ($this->filters['has_photos']) {
            $query->whereHas('photos');
        }

        if ($this->filters['verified_only']) {
            $query->where('is_verified', true);
        }

        if ($this->filters['online_only']) {
            $query->where('is_currently_online', true);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'age':
                $query->orderBy('profiles.date_of_birth', 'asc');
                break;
            case 'distance':
                // TODO: Implement distance sorting
                $query->orderBy('created_at', 'desc');
                break;
            default: // relevance
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate(12);
    }

    public function render()
    {
        return view('livewire.pages.search-page')
            ->layout('layouts.app', ['title' => 'Search & Discover']);
    }
}
