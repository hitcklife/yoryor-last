<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VerificationPage extends Component
{
    use WithFileUploads;

    public $verificationStatus = [];
    public $activeTab = 'photo';
    public $photoVerification = null;
    public $idVerification = null;
    public $phoneNumber = '';
    public $emailVerified = false;
    public $uploadProgress = 0;

    protected $queryString = [
        'activeTab' => ['except' => 'photo'],
    ];

    public function mount()
    {
        $this->loadVerificationStatus();
    }

    public function loadVerificationStatus()
    {
        $user = Auth::user();
        
        $this->verificationStatus = [
            'photo' => [
                'status' => 'pending', // pending, verified, rejected
                'submitted_at' => null,
                'verified_at' => null,
                'rejection_reason' => null
            ],
            'id' => [
                'status' => 'not_started',
                'submitted_at' => null,
                'verified_at' => null,
                'rejection_reason' => null
            ],
            'phone' => [
                'status' => $user->phone_verified_at ? 'verified' : 'not_started',
                'verified_at' => $user->phone_verified_at,
                'phone_number' => $user->phone
            ],
            'email' => [
                'status' => $user->email_verified_at ? 'verified' : 'not_started',
                'verified_at' => $user->email_verified_at
            ]
        ];

        $this->phoneNumber = $user->phone ?? '';
        $this->emailVerified = $user->email_verified_at ? true : false;
    }

    public function updatedPhotoVerification()
    {
        $this->validate([
            'photoVerification' => 'image|max:10240', // 10MB max
        ]);

        $this->uploadProgress = 0;
        $this->uploadPhoto();
    }

    public function updatedIdVerification()
    {
        $this->validate([
            'idVerification' => 'image|max:10240', // 10MB max
        ]);

        $this->uploadProgress = 0;
        $this->uploadId();
    }

    public function uploadPhoto()
    {
        if (!$this->photoVerification) return;

        try {
            // TODO: Implement actual photo verification logic
            $path = $this->photoVerification->store('verifications/photos', 'public');
            
            // Simulate upload progress
            for ($i = 0; $i <= 100; $i += 10) {
                $this->uploadProgress = $i;
                usleep(100000); // 0.1 second delay
            }

            $this->verificationStatus['photo']['status'] = 'pending';
            $this->verificationStatus['photo']['submitted_at'] = now();
            
            session()->flash('success', 'Photo verification submitted successfully! We\'ll review it within 24 hours.');
            $this->photoVerification = null;
            $this->uploadProgress = 0;

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload photo. Please try again.');
        }
    }

    public function uploadId()
    {
        if (!$this->idVerification) return;

        try {
            // TODO: Implement actual ID verification logic
            $path = $this->idVerification->store('verifications/ids', 'public');
            
            // Simulate upload progress
            for ($i = 0; $i <= 100; $i += 10) {
                $this->uploadProgress = $i;
                usleep(100000); // 0.1 second delay
            }

            $this->verificationStatus['id']['status'] = 'pending';
            $this->verificationStatus['id']['submitted_at'] = now();
            
            session()->flash('success', 'ID verification submitted successfully! We\'ll review it within 24 hours.');
            $this->idVerification = null;
            $this->uploadProgress = 0;

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload ID. Please try again.');
        }
    }

    public function sendPhoneVerification()
    {
        $this->validate([
            'phoneNumber' => 'required|regex:/^\+[1-9]\d{1,14}$/',
        ]);

        // TODO: Implement actual SMS verification
        session()->flash('success', 'Verification code sent to ' . $this->phoneNumber);
    }

    public function verifyPhoneCode($code)
    {
        // TODO: Implement actual code verification
        if ($code === '123456') { // Mock verification
            $this->verificationStatus['phone']['status'] = 'verified';
            $this->verificationStatus['phone']['verified_at'] = now();
            session()->flash('success', 'Phone number verified successfully!');
        } else {
            session()->flash('error', 'Invalid verification code. Please try again.');
        }
    }

    public function resendEmailVerification()
    {
        // TODO: Implement actual email verification resend
        session()->flash('success', 'Verification email sent! Please check your inbox.');
    }

    public function getVerificationBadges()
    {
        $badges = [];
        
        foreach ($this->verificationStatus as $type => $status) {
            if ($status['status'] === 'verified') {
                $badges[] = [
                    'type' => $type,
                    'name' => ucfirst($type) . ' Verified',
                    'icon' => $this->getVerificationIcon($type),
                    'color' => 'green'
                ];
            }
        }
        
        return $badges;
    }

    public function getVerificationIcon($type)
    {
        return match($type) {
            'photo' => 'camera',
            'id' => 'id-card',
            'phone' => 'phone',
            'email' => 'mail',
            default => 'check'
        };
    }

    public function getVerificationColor($status)
    {
        return match($status) {
            'verified' => 'text-green-600 bg-green-100',
            'pending' => 'text-yellow-600 bg-yellow-100',
            'rejected' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function getVerificationStatusText($status)
    {
        return match($status) {
            'verified' => 'Verified',
            'pending' => 'Under Review',
            'rejected' => 'Rejected',
            default => 'Not Started'
        };
    }

    public function getOverallVerificationScore()
    {
        $verifiedCount = collect($this->verificationStatus)->where('status', 'verified')->count();
        $totalCount = count($this->verificationStatus);
        
        return round(($verifiedCount / $totalCount) * 100);
    }

    public function render()
    {
        return view('livewire.pages.verification-page')
            ->layout('layouts.app', ['title' => 'Account Verification']);
    }
}
