<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\MatchModel;
use App\Models\Message;
use App\Models\Like;
use App\Models\UserReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $totalUsers;
    public $activeUsers;
    public $totalMatches;
    public $totalMessages;
    public $totalLikes;
    public $totalReports;
    public $newUsersToday;
    public $messagesThisWeek;
    public $recentActivity = [];
    public $userGrowthData = [];
    public $topCountries = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadRecentActivity();
        $this->loadUserGrowth();
        $this->loadTopCountries();
    }

    public function loadStats()
    {
        $this->totalUsers = User::count();
        $this->activeUsers = User::where('last_active_at', '>=', now()->subDays(30))->count();
        $this->totalMatches = MatchModel::count();
        $this->totalMessages = Message::count();
        $this->totalLikes = Like::count();
        $this->totalReports = UserReport::count();
        $this->newUsersToday = User::whereDate('created_at', today())->count();
        $this->messagesThisWeek = Message::where('created_at', '>=', now()->subWeek())->count();
    }

    public function loadRecentActivity()
    {
        $this->recentActivity = collect([
            [
                'type' => 'user_joined',
                'message' => 'New user registered',
                'count' => User::whereDate('created_at', today())->count(),
                'time' => 'Today',
                'icon' => 'user-plus',
                'color' => 'green'
            ],
            [
                'type' => 'matches_made',
                'message' => 'New matches created',
                'count' => MatchModel::whereDate('created_at', today())->count(),
                'time' => 'Today',
                'icon' => 'heart',
                'color' => 'pink'
            ],
            [
                'type' => 'messages_sent',
                'message' => 'Messages exchanged',
                'count' => Message::whereDate('created_at', today())->count(),
                'time' => 'Today',
                'icon' => 'chat-bubble',
                'color' => 'blue'
            ],
            [
                'type' => 'reports_filed',
                'message' => 'Reports submitted',
                'count' => UserReport::whereDate('created_at', today())->count(),
                'time' => 'Today',
                'icon' => 'flag',
                'color' => 'red'
            ],
        ]);
    }

    public function loadUserGrowth()
    {
        $this->userGrowthData = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d'),
                'count' => $item->count
            ];
        });
    }

    public function loadTopCountries()
    {
        $this->topCountries = User::join('profiles', 'users.id', '=', 'profiles.user_id')
            ->join('countries', 'profiles.country_id', '=', 'countries.id')
            ->select('countries.name', DB::raw('COUNT(*) as user_count'))
            ->groupBy('countries.id', 'countries.name')
            ->orderByDesc('user_count')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard')->layout('components.layouts.admin', ['title' => 'Dashboard']);
    }
}