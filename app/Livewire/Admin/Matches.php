<?php

namespace App\Livewire\Admin;

use App\Models\MatchModel;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Matches extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
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

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function deleteMatch($matchId)
    {
        $match = MatchModel::findOrFail($matchId);
        $match->delete();
        
        $this->dispatch('match-deleted', [
            'message' => 'Match deleted successfully'
        ]);
    }

    public function render()
    {
        $matchesQuery = MatchModel::with(['user.profile', 'matchedUser.profile'])
            ->when($this->search, function($query) {
                $query->whereHas('user.profile', function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('matchedUser.profile', function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('user', function($q) {
                    $q->where('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('matchedUser', function($q) {
                    $q->where('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $matches = $matchesQuery->paginate($this->perPage);

        // Statistics
        $totalMatches = MatchModel::count();
        $matchesToday = MatchModel::whereDate('created_at', today())->count();
        $matchesThisWeek = MatchModel::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $matchesThisMonth = MatchModel::whereMonth('created_at', now()->month)->count();

        return view('livewire.admin.matches', [
            'matches' => $matches,
            'totalMatches' => $totalMatches,
            'matchesToday' => $matchesToday,
            'matchesThisWeek' => $matchesThisWeek,
            'matchesThisMonth' => $matchesThisMonth,
        ])->layout('components.layouts.admin', ['title' => 'Matches Management']);
    }
}