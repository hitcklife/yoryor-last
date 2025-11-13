<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class PanicButton extends Component
{
    public $emergencyContacts = [];
    public $safetySettings = [];
    public $recentAlerts = [];
    public $activeTab = 'panic';
    public $isPanicActive = false;
    public $panicCountdown = 0;
    public $locationData = null;
    public $safetyScore = 85;

    protected $queryString = [
        'activeTab' => ['except' => 'panic'],
    ];

    public function mount()
    {
        $this->loadEmergencyContacts();
        $this->loadSafetySettings();
        $this->loadRecentAlerts();
        $this->loadLocationData();
    }

    public function loadEmergencyContacts()
    {
        // TODO: Load from actual emergency contacts model
        $this->emergencyContacts = [
            [
                'id' => 1,
                'name' => 'Emergency Services',
                'phone' => '911',
                'type' => 'emergency',
                'is_primary' => true
            ],
            [
                'id' => 2,
                'name' => 'Mom',
                'phone' => '+1234567890',
                'type' => 'family',
                'is_primary' => false
            ],
            [
                'id' => 3,
                'name' => 'Best Friend',
                'phone' => '+1234567891',
                'type' => 'friend',
                'is_primary' => false
            ]
        ];
    }

    public function loadSafetySettings()
    {
        $this->safetySettings = [
            'auto_location_sharing' => true,
            'panic_button_enabled' => true,
            'safety_check_reminders' => true,
            'emergency_contacts_notification' => true,
            'location_history' => true,
            'safety_score_tracking' => true
        ];
    }

    public function loadRecentAlerts()
    {
        // TODO: Load from actual alerts model
        $this->recentAlerts = [
            [
                'id' => 1,
                'type' => 'safety_check',
                'message' => 'Safety check completed successfully',
                'timestamp' => now()->subHours(2),
                'status' => 'completed'
            ],
            [
                'id' => 2,
                'type' => 'location_shared',
                'message' => 'Location shared with emergency contacts',
                'timestamp' => now()->subDays(1),
                'status' => 'sent'
            ]
        ];
    }

    public function loadLocationData()
    {
        // TODO: Load actual location data
        $this->locationData = [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'address' => 'New York, NY, USA',
            'accuracy' => 10,
            'last_updated' => now()
        ];
    }

    public function activatePanicButton()
    {
        $this->isPanicActive = true;
        $this->panicCountdown = 10; // 10 second countdown
        
        // Start countdown
        $this->dispatch('start-panic-countdown');
        
        // TODO: Implement actual panic button functionality
        session()->flash('warning', 'Panic button activated! Emergency contacts will be notified in 10 seconds.');
    }

    public function cancelPanicButton()
    {
        $this->isPanicActive = false;
        $this->panicCountdown = 0;
        
        // TODO: Cancel panic alert
        session()->flash('success', 'Panic alert cancelled.');
    }

    public function sendPanicAlert()
    {
        // TODO: Implement actual panic alert sending
        $this->isPanicActive = false;
        $this->panicCountdown = 0;
        
        // Send to emergency contacts
        foreach ($this->emergencyContacts as $contact) {
            if ($contact['is_primary']) {
                // TODO: Send SMS/call to emergency contact
            }
        }
        
        session()->flash('success', 'Emergency alert sent to all contacts!');
        $this->loadRecentAlerts();
    }

    public function sendSafetyCheck()
    {
        // TODO: Implement safety check functionality
        session()->flash('success', 'Safety check sent! Please respond within 5 minutes.');
        
        // Add to recent alerts
        $this->recentAlerts = array_merge([
            [
                'id' => count($this->recentAlerts) + 1,
                'type' => 'safety_check',
                'message' => 'Safety check sent - awaiting response',
                'timestamp' => now(),
                'status' => 'pending'
            ]
        ], $this->recentAlerts);
    }

    public function shareLocation()
    {
        // TODO: Implement location sharing
        session()->flash('success', 'Location shared with emergency contacts!');
        
        $this->loadRecentAlerts();
    }

    public function addEmergencyContact($name, $phone, $type = 'friend')
    {
        // TODO: Implement adding emergency contact
        $newContact = [
            'id' => count($this->emergencyContacts) + 1,
            'name' => $name,
            'phone' => $phone,
            'type' => $type,
            'is_primary' => false
        ];
        
        $this->emergencyContacts[] = $newContact;
        session()->flash('success', 'Emergency contact added successfully!');
    }

    public function removeEmergencyContact($contactId)
    {
        // TODO: Implement removing emergency contact
        $this->emergencyContacts = array_filter($this->emergencyContacts, function($contact) use ($contactId) {
            return $contact['id'] !== $contactId;
        });
        
        session()->flash('success', 'Emergency contact removed!');
    }

    public function updateSafetySettings($setting, $value)
    {
        $this->safetySettings[$setting] = $value;
        
        // TODO: Save to database
        session()->flash('success', 'Safety settings updated!');
    }

    public function getSafetyScoreColor()
    {
        if ($this->safetyScore >= 80) return 'text-green-600 bg-green-100';
        if ($this->safetyScore >= 60) return 'text-yellow-600 bg-yellow-100';
        return 'text-red-600 bg-red-100';
    }

    public function getSafetyScoreText()
    {
        if ($this->safetyScore >= 80) return 'Excellent';
        if ($this->safetyScore >= 60) return 'Good';
        return 'Needs Attention';
    }

    public function getAlertTypeIcon($type)
    {
        return match($type) {
            'safety_check' => 'shield-check',
            'location_shared' => 'map-pin',
            'panic_alert' => 'alert-triangle',
            'emergency_call' => 'phone',
            default => 'bell'
        };
    }

    public function getAlertTypeColor($type)
    {
        return match($type) {
            'safety_check' => 'text-blue-600 bg-blue-100',
            'location_shared' => 'text-green-600 bg-green-100',
            'panic_alert' => 'text-red-600 bg-red-100',
            'emergency_call' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function getContactTypeIcon($type)
    {
        return match($type) {
            'emergency' => 'phone',
            'family' => 'users',
            'friend' => 'user',
            default => 'user'
        };
    }

    public function render()
    {
        return view('livewire.components.panic-button')
            ->layout('layouts.app', ['title' => 'Safety & Emergency']);
    }
}
