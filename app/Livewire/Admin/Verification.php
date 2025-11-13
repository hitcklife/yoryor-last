<?php

namespace App\Livewire\Admin;

use App\Models\VerificationRequest;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Verification extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $sortBy = 'submitted_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    // Review modal
    public $selectedRequest = null;
    public $showReviewModal = false;
    public $reviewAction = '';
    public $reviewFeedback = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'sortBy' => ['except' => 'submitted_at'],
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

    public function updatingTypeFilter()
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
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function reviewRequest($requestId, $action)
    {
        $this->selectedRequest = VerificationRequest::with(['user.profile'])->findOrFail($requestId);
        $this->reviewAction = $action;
        $this->reviewFeedback = '';
        $this->showReviewModal = true;
    }

    public function submitReview()
    {
        $this->validate([
            'reviewFeedback' => $this->reviewAction === 'approve' ? 'nullable|string|max:1000' : 'required|string|max:1000',
        ]);

        $reviewer = auth()->user();

        switch ($this->reviewAction) {
            case 'approve':
                $this->selectedRequest->approve($reviewer, $this->reviewFeedback);
                $message = 'Verification request approved successfully';
                break;
            case 'reject':
                $this->selectedRequest->reject($reviewer, $this->reviewFeedback);
                $message = 'Verification request rejected';
                break;
            case 'needs_review':
                $this->selectedRequest->markNeedsReview($reviewer, $this->reviewFeedback);
                $message = 'Verification marked as needing additional review';
                break;
        }

        $this->closeReviewModal();
        $this->dispatch('verification-reviewed', ['message' => $message]);
    }

    public function closeReviewModal()
    {
        $this->showReviewModal = false;
        $this->selectedRequest = null;
        $this->reviewAction = '';
        $this->reviewFeedback = '';
    }

    public function render()
    {
        $verificationsQuery = VerificationRequest::with(['user.profile', 'reviewedBy.profile'])
            ->when($this->search, function($query) {
                $query->whereHas('user.profile', function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('user', function($q) {
                    $q->where('email', 'like', '%' . $this->search . '%');
                })
                ->orWhere('verification_type', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function($query) {
                $query->where('verification_type', $this->typeFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $verifications = $verificationsQuery->paginate($this->perPage);

        // Statistics
        $totalRequests = VerificationRequest::count();
        $pendingRequests = VerificationRequest::where('status', 'pending')->count();
        $approvedRequests = VerificationRequest::where('status', 'approved')->count();
        $rejectedRequests = VerificationRequest::where('status', 'rejected')->count();
        $needsReviewRequests = VerificationRequest::where('status', 'needs_review')->count();
        $requestsToday = VerificationRequest::whereDate('submitted_at', today())->count();

        // Verification types breakdown
        $verificationTypes = VerificationRequest::selectRaw('verification_type, COUNT(*) as count')
                                               ->groupBy('verification_type')
                                               ->orderBy('count', 'desc')
                                               ->get();

        // Available types
        $availableTypes = [
            'identity' => 'Identity Verification',
            'photo' => 'Photo Verification',
            'employment' => 'Employment Verification',
            'education' => 'Education Verification',
            'income' => 'Income Verification',
            'address' => 'Address Verification',
            'social_media' => 'Social Media Verification',
            'background_check' => 'Background Check',
        ];

        return view('livewire.admin.verification', [
            'verifications' => $verifications,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'needsReviewRequests' => $needsReviewRequests,
            'requestsToday' => $requestsToday,
            'verificationTypes' => $verificationTypes,
            'availableTypes' => $availableTypes,
        ])->layout('components.layouts.admin', ['title' => 'Verification Management']);
    }
}