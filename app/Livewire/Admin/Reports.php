<?php

namespace App\Livewire\Admin;

use App\Models\UserReport;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $reasonFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'reasonFilter' => ['except' => ''],
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

    public function updatingReasonFilter()
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
        $this->reasonFilter = '';
        $this->resetPage();
    }

    public function resolveReport($reportId)
    {
        $report = UserReport::findOrFail($reportId);
        $report->update(['status' => 'resolved']);
        
        $this->dispatch('report-resolved', [
            'message' => 'Report resolved successfully'
        ]);
    }

    public function dismissReport($reportId)
    {
        $report = UserReport::findOrFail($reportId);
        $report->update(['status' => 'dismissed']);
        
        $this->dispatch('report-dismissed', [
            'message' => 'Report dismissed successfully'
        ]);
    }

    public function suspendUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['disabled_at' => now()]);
        
        $this->dispatch('user-suspended', [
            'message' => 'User suspended successfully'
        ]);
    }

    public function render()
    {
        $reportsQuery = UserReport::with(['reporter.profile', 'reported.profile'])
            ->when($this->search, function($query) {
                $query->whereHas('reporter.profile', function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('reported.profile', function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('reporter', function($q) {
                    $q->where('email', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('reported', function($q) {
                    $q->where('email', 'like', '%' . $this->search . '%');
                })
                ->orWhere('reason', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->reasonFilter, function($query) {
                $query->where('reason', $this->reasonFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $reports = $reportsQuery->paginate($this->perPage);

        // Statistics
        $totalReports = UserReport::count();
        $pendingReports = UserReport::where('status', 'pending')->count();
        $resolvedReports = UserReport::where('status', 'resolved')->count();
        $dismissedReports = UserReport::where('status', 'dismissed')->count();
        $reportsToday = UserReport::whereDate('created_at', today())->count();
        $reportsThisWeek = UserReport::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        // Report reasons breakdown
        $reportReasons = UserReport::selectRaw('reason, COUNT(*) as count')
                                  ->groupBy('reason')
                                  ->orderBy('count', 'desc')
                                  ->get();

        return view('livewire.admin.reports', [
            'reports' => $reports,
            'totalReports' => $totalReports,
            'pendingReports' => $pendingReports,
            'resolvedReports' => $resolvedReports,
            'dismissedReports' => $dismissedReports,
            'reportsToday' => $reportsToday,
            'reportsThisWeek' => $reportsThisWeek,
            'reportReasons' => $reportReasons,
            'availableReasons' => UserReport::getReportReasons(),
        ])->layout('components.layouts.admin', ['title' => 'Reports & Safety']);
    }
}