<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class InsightsPage extends Component
{
    use WithPagination;

    public $activeTab = 'overview';
    public $dateRange = '30'; // days
    public $insights = [];
    public $profileViews = [];
    public $matchStats = [];
    public $messageStats = [];
    public $activityStats = [];
    public $demographics = [];
    public $successMetrics = [];

    protected $queryString = [
        'activeTab' => ['except' => 'overview'],
        'dateRange' => ['except' => '30'],
    ];

    public function mount()
    {
        $this->loadInsights();
    }

    public function updatedDateRange()
    {
        $this->loadInsights();
    }

    public function loadInsights()
    {
        $this->loadProfileViews();
        $this->loadMatchStats();
        $this->loadMessageStats();
        $this->loadActivityStats();
        $this->loadDemographics();
        $this->loadSuccessMetrics();
    }

    public function loadProfileViews()
    {
        // TODO: Load from actual analytics data
        $this->profileViews = [
            'total_views' => 1247,
            'unique_views' => 892,
            'views_today' => 23,
            'views_this_week' => 156,
            'views_this_month' => 634,
            'peak_hour' => '8:00 PM',
            'peak_day' => 'Friday',
            'trend' => '+12%', // compared to previous period
            'chart_data' => [
                ['date' => '2024-01-01', 'views' => 45],
                ['date' => '2024-01-02', 'views' => 52],
                ['date' => '2024-01-03', 'views' => 38],
                ['date' => '2024-01-04', 'views' => 61],
                ['date' => '2024-01-05', 'views' => 73],
                ['date' => '2024-01-06', 'views' => 89],
                ['date' => '2024-01-07', 'views' => 67],
            ]
        ];
    }

    public function loadMatchStats()
    {
        $this->matchStats = [
            'total_matches' => 47,
            'matches_this_week' => 8,
            'matches_this_month' => 23,
            'match_rate' => 3.8, // percentage
            'like_to_match_ratio' => 0.12,
            'super_likes_sent' => 12,
            'super_like_success_rate' => 0.75,
            'matches_by_day' => [
                'Monday' => 3,
                'Tuesday' => 5,
                'Wednesday' => 4,
                'Thursday' => 6,
                'Friday' => 8,
                'Saturday' => 12,
                'Sunday' => 9
            ],
            'top_interests' => [
                'Travel' => 15,
                'Music' => 12,
                'Fitness' => 10,
                'Art' => 8,
                'Food' => 7
            ]
        ];
    }

    public function loadMessageStats()
    {
        $this->messageStats = [
            'total_messages_sent' => 342,
            'total_messages_received' => 298,
            'conversations_started' => 23,
            'response_rate' => 0.68,
            'average_response_time' => '2.5 hours',
            'longest_conversation' => 47,
            'messages_this_week' => 45,
            'messages_this_month' => 189,
            'most_active_day' => 'Saturday',
            'most_active_hour' => '9:00 PM',
            'conversation_starter_success' => 0.74
        ];
    }

    public function loadActivityStats()
    {
        $this->activityStats = [
            'total_swipes' => 2847,
            'likes_given' => 234,
            'passes_given' => 2613,
            'likes_received' => 189,
            'passes_received' => 45,
            'profile_completeness' => 87,
            'photos_uploaded' => 6,
            'bio_length' => 142,
            'interests_added' => 8,
            'last_active' => now()->subHours(2),
            'streak_days' => 12
        ];
    }

    public function loadDemographics()
    {
        $this->demographics = [
            'age_distribution' => [
                '18-25' => 35,
                '26-35' => 45,
                '36-45' => 15,
                '46+' => 5
            ],
            'gender_distribution' => [
                'Male' => 60,
                'Female' => 35,
                'Non-binary' => 5
            ],
            'location_distribution' => [
                'Local (0-10km)' => 45,
                'Regional (10-50km)' => 35,
                'National (50km+)' => 20
            ],
            'education_levels' => [
                'High School' => 15,
                'Bachelor\'s' => 45,
                'Master\'s' => 30,
                'PhD' => 10
            ],
            'profession_categories' => [
                'Technology' => 25,
                'Healthcare' => 20,
                'Education' => 15,
                'Business' => 20,
                'Creative' => 10,
                'Other' => 10
            ]
        ];
    }

    public function loadSuccessMetrics()
    {
        $this->successMetrics = [
            'overall_success_score' => 78,
            'profile_attractiveness' => 82,
            'engagement_rate' => 65,
            'conversation_quality' => 71,
            'match_quality' => 74,
            'response_consistency' => 68,
            'profile_completeness' => 87,
            'photo_quality' => 79,
            'bio_effectiveness' => 73,
            'interest_alignment' => 76,
            'recommendations' => [
                'Add more photos to increase profile views by 15%',
                'Update your bio to include more conversation starters',
                'Consider adding your profession to attract career-focused matches',
                'Your peak activity time is 8-10 PM - try being more active then',
                'Travel and Music are your most successful interests - highlight them more'
            ]
        ];
    }

    public function getDateRangeLabel()
    {
        return match($this->dateRange) {
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
            '365' => 'Last year',
            default => 'Last 30 days'
        };
    }

    public function getSuccessScoreColor($score)
    {
        if ($score >= 80) return 'text-green-600 bg-green-100';
        if ($score >= 60) return 'text-yellow-600 bg-yellow-100';
        return 'text-red-600 bg-red-100';
    }

    public function getSuccessScoreText($score)
    {
        if ($score >= 80) return 'Excellent';
        if ($score >= 60) return 'Good';
        return 'Needs Improvement';
    }

    public function getTrendColor($trend)
    {
        if (str_starts_with($trend, '+')) return 'text-green-600';
        if (str_starts_with($trend, '-')) return 'text-red-600';
        return 'text-gray-600';
    }

    public function getTrendIcon($trend)
    {
        if (str_starts_with($trend, '+')) return 'trending-up';
        if (str_starts_with($trend, '-')) return 'trending-down';
        return 'minus';
    }

    public function exportData($format = 'csv')
    {
        // TODO: Implement data export functionality
        session()->flash('success', 'Data exported successfully!');
    }

    public function generateReport()
    {
        // TODO: Implement report generation
        session()->flash('success', 'Report generated successfully!');
    }

    public function getTopPerformingPhotos()
    {
        // TODO: Load from actual photo analytics
        return [
            [
                'id' => 1,
                'url' => '/placeholder.jpg',
                'views' => 234,
                'likes' => 45,
                'engagement_rate' => 19.2
            ],
            [
                'id' => 2,
                'url' => '/placeholder.jpg',
                'views' => 189,
                'likes' => 38,
                'engagement_rate' => 20.1
            ]
        ];
    }

    public function getConversationInsights()
    {
        // TODO: Load from actual conversation analytics
        return [
            'average_conversation_length' => 12.5,
            'most_common_opener' => 'Hey! How\'s your day going?',
            'best_response_time' => '2-4 hours',
            'conversation_success_rate' => 0.68,
            'most_engaging_topics' => [
                'Travel' => 0.85,
                'Music' => 0.78,
                'Food' => 0.72,
                'Movies' => 0.69,
                'Fitness' => 0.65
            ]
        ];
    }

    public function render()
    {
        return view('livewire.pages.insights-page')
            ->layout('layouts.app', ['title' => 'Insights & Analytics']);
    }
}
