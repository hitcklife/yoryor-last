<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\MatchModel;
use App\Models\Message;
use App\Models\Like;
use App\Models\UserReport;
use App\Models\VerificationRequest;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Analytics extends Component
{
    public $dateRange = '30';
    public $selectedMetric = 'users';

    // Data properties
    public $userGrowthData = [];
    public $matchesData = [];
    public $messagesData = [];
    public $topCountries = [];
    public $genderBreakdown = [];
    public $ageGroups = [];

    public function mount()
    {
        $this->loadAnalyticsData();
    }

    public function updatedDateRange()
    {
        $this->loadAnalyticsData();
    }

    public function updatedSelectedMetric()
    {
        $this->loadAnalyticsData();
    }

    public function loadAnalyticsData()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        // User Growth Data
        $this->userGrowthData = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d'),
                'count' => $item->count
            ];
        });

        // Matches Data
        $this->matchesData = MatchModel::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d'),
                'count' => $item->count
            ];
        });

        // Messages Data
        $this->messagesData = Message::select(
            DB::raw('DATE(sent_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('sent_at', '>=', $startDate)
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d'),
                'count' => $item->count
            ];
        });

        // Top Countries
        $this->topCountries = User::join('profiles', 'users.id', '=', 'profiles.user_id')
            ->select('profiles.country_name', DB::raw('COUNT(*) as user_count'))
            ->whereNotNull('profiles.country_name')
            ->groupBy('profiles.country_name')
            ->orderByDesc('user_count')
            ->limit(10)
            ->get();

        // Gender Breakdown
        $this->genderBreakdown = User::join('profiles', 'users.id', '=', 'profiles.user_id')
            ->select('profiles.gender', DB::raw('COUNT(*) as count'))
            ->whereNotNull('profiles.gender')
            ->groupBy('profiles.gender')
            ->get();

        // Age Groups
        $this->ageGroups = User::join('profiles', 'users.id', '=', 'profiles.user_id')
            ->select(
                DB::raw('
                    CASE 
                        WHEN EXTRACT(YEAR FROM AGE(profiles.date_of_birth)) BETWEEN 18 AND 24 THEN \'18-24\'
                        WHEN EXTRACT(YEAR FROM AGE(profiles.date_of_birth)) BETWEEN 25 AND 34 THEN \'25-34\'
                        WHEN EXTRACT(YEAR FROM AGE(profiles.date_of_birth)) BETWEEN 35 AND 44 THEN \'35-44\'
                        WHEN EXTRACT(YEAR FROM AGE(profiles.date_of_birth)) BETWEEN 45 AND 54 THEN \'45-54\'
                        WHEN EXTRACT(YEAR FROM AGE(profiles.date_of_birth)) >= 55 THEN \'55+\'
                        ELSE \'Unknown\'
                    END as age_group
                '),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('profiles.date_of_birth')
            ->groupBy('age_group')
            ->orderByRaw('
                CASE age_group
                    WHEN "18-24" THEN 1
                    WHEN "25-34" THEN 2
                    WHEN "35-44" THEN 3
                    WHEN "45-54" THEN 4
                    WHEN "55+" THEN 5
                    ELSE 6
                END
            ')
            ->get();
    }

    public function render()
    {
        $days = (int) $this->dateRange;
        $startDate = now()->subDays($days);

        // Summary Statistics
        $totalUsers = User::count();
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        $activeUsers = User::where('last_active_at', '>=', now()->subDays(30))->count();
        
        $totalMatches = MatchModel::count();
        $newMatches = MatchModel::where('created_at', '>=', $startDate)->count();
        
        $totalMessages = Message::count();
        $newMessages = Message::where('sent_at', '>=', $startDate)->count();
        
        $totalLikes = Like::count();
        $newLikes = Like::where('created_at', '>=', $startDate)->count();

        $totalReports = UserReport::count();
        $pendingReports = UserReport::where('status', 'pending')->count();
        
        $totalVerifications = VerificationRequest::count();
        $pendingVerifications = VerificationRequest::where('status', 'pending')->count();

        // Engagement Metrics
        $averageMessagesPerUser = $totalUsers > 0 ? round($totalMessages / $totalUsers, 1) : 0;
        $matchRate = $totalLikes > 0 ? round(($totalMatches / $totalLikes) * 100, 1) : 0;
        
        // Most Active Days
        $mostActiveDay = Message::select(
            DB::raw('DAYNAME(sent_at) as day'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('day')
        ->orderByDesc('count')
        ->first();

        // Peak Hours
        $peakHour = Message::select(
            DB::raw('HOUR(sent_at) as hour'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('hour')
        ->orderByDesc('count')
        ->first();

        return view('livewire.admin.analytics', [
            'totalUsers' => $totalUsers,
            'newUsers' => $newUsers,
            'activeUsers' => $activeUsers,
            'totalMatches' => $totalMatches,
            'newMatches' => $newMatches,
            'totalMessages' => $totalMessages,
            'newMessages' => $newMessages,
            'totalLikes' => $totalLikes,
            'newLikes' => $newLikes,
            'totalReports' => $totalReports,
            'pendingReports' => $pendingReports,
            'totalVerifications' => $totalVerifications,
            'pendingVerifications' => $pendingVerifications,
            'averageMessagesPerUser' => $averageMessagesPerUser,
            'matchRate' => $matchRate,
            'mostActiveDay' => $mostActiveDay,
            'peakHour' => $peakHour,
        ])->layout('components.layouts.admin', ['title' => 'Analytics & Insights']);
    }
}