<?php

namespace App\Livewire\Pages;

use App\Models\Block;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class BlockedUsersPage extends Component
{
    use WithPagination;

    public $blockedUsers = [];
    public $reports = [];
    public $activeTab = 'blocked';
    public $searchTerm = '';

    protected $queryString = [
        'activeTab' => ['except' => 'blocked'],
        'searchTerm' => ['except' => ''],
    ];

    public function mount()
    {
        $this->loadBlockedUsers();
        $this->loadReports();
    }

    public function loadBlockedUsers()
    {
        $this->blockedUsers = Block::where('blocker_id', Auth::id())
            ->with(['blockedUser.profile', 'blockedUser.photos'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadReports()
    {
        // TODO: Implement reports loading when Report model is created
        $this->reports = collect();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->loadBlockedUsers();
    }

    public function unblockUser($blockId)
    {
        $block = Block::where('id', $blockId)
            ->where('blocker_id', Auth::id())
            ->first();

        if ($block) {
            $block->delete();
            $this->loadBlockedUsers();
            
            session()->flash('success', 'User unblocked successfully');
        }
    }

    public function blockUser($userId)
    {
        // Check if already blocked
        $existingBlock = Block::where('blocker_id', Auth::id())
            ->where('blocked_user_id', $userId)
            ->first();

        if (!$existingBlock) {
            Block::create([
                'blocker_id' => Auth::id(),
                'blocked_user_id' => $userId,
                'reason' => 'User requested block',
            ]);

            $this->loadBlockedUsers();
            session()->flash('success', 'User blocked successfully');
        }
    }

    public function reportUser($userId, $reason = null)
    {
        // TODO: Implement report functionality when Report model is created
        session()->flash('success', 'User reported successfully');
    }

    public function getFilteredBlockedUsers()
    {
        if (empty($this->searchTerm)) {
            return $this->blockedUsers;
        }

        return $this->blockedUsers->filter(function ($block) {
            $user = $block->blockedUser;
            $searchLower = strtolower($this->searchTerm);
            
            return str_contains(strtolower($user->name ?? ''), $searchLower) ||
                   str_contains(strtolower($user->profile?->first_name ?? ''), $searchLower) ||
                   str_contains(strtolower($user->profile?->last_name ?? ''), $searchLower);
        });
    }

    public function getSafetyScore($userId)
    {
        // TODO: Implement safety score calculation
        return rand(70, 95);
    }

    public function render()
    {
        return view('livewire.pages.blocked-users-page')
            ->layout('layouts.app', ['title' => 'Blocked Users & Reports']);
    }
}
