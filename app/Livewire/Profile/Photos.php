<?php

namespace App\Livewire\Profile;

use App\Services\AuthService;
use App\Services\MediaUploadService;
use App\Services\ImageProcessingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class Photos extends Component
{
    use WithFileUploads;

    public $photos = [];
    public $profilePhotoIndex = 0;
    public $isPrivate = false;
    public $isLoading = false;
    public $errorMessage = '';
    public $uploadProgress = [];

    protected $rules = [
        'photos' => 'required|array|min:1|max:6',
        'photos.*' => 'image|max:5120', // 5MB max per photo
    ];

    protected $messages = [
        'photos.required' => 'Please upload at least 1 main photo.',
        'photos.min' => 'Please upload at least 1 main photo.',
        'photos.max' => 'You can upload a maximum of 6 photos (1 main + 5 extra).',
        'photos.*.image' => 'All files must be images.',
        'photos.*.max' => 'Each photo must be smaller than 5MB.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $user = Auth::user()->load(['profile', 'photos']);

        // If registration is already completed, redirect to dashboard
        if ($user->registration_completed && $user->profile?->first_name && $user->photos->count() >= 1) {
            return redirect()->route('dashboard');
        }

        // REMOVED validation - just let user proceed

        // Use already loaded photos
        $existingPhotos = $user->photos->sortBy('order');
        
        // Check if any existing photos are private
        if ($existingPhotos->count() > 0) {
            $this->isPrivate = $existingPhotos->where('is_private', true)->count() > 0;
        }
        
        foreach ($existingPhotos as $index => $photo) {
            $this->photos[$index] = $photo->original_url;
            if ($photo->is_profile_photo) {
                $this->profilePhotoIndex = $index;
            }
        }
    }

    public function updatedPhotos()
    {
        $this->validate([
            'photos' => 'array|min:1|max:6',
            'photos.*' => 'image|max:5120'
        ]);
    }

    public function removePhoto($index)
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos); // Re-index array
        
        // Adjust profile photo index if needed
        if ($this->profilePhotoIndex >= $index) {
            $this->profilePhotoIndex = max(0, $this->profilePhotoIndex - 1);
        }
    }

    public function setProfilePhoto($index)
    {
        if (isset($this->photos[$index])) {
            $this->profilePhotoIndex = $index;
        }
    }

    public function submit()
    {
        // Custom validation for mixed photos (new uploads and existing URLs)
        $newPhotos = array_filter($this->photos, function($photo) {
            return !is_string($photo) || !filter_var($photo, FILTER_VALIDATE_URL);
        });
        
        if (count($this->photos) < 1) {
            $this->errorMessage = 'Please upload at least 1 photo.';
            return;
        }
        
        if (count($this->photos) > 6) {
            $this->errorMessage = 'You can upload a maximum of 6 photos.';
            return;
        }
        
        // Only validate new uploads as images
        foreach ($newPhotos as $photo) {
            if ($photo && !in_array($photo->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])) {
                $this->errorMessage = 'All files must be images.';
                return;
            }
            if ($photo && $photo->getSize() > 5120000) { // 5MB
                $this->errorMessage = 'Each photo must be smaller than 5MB.';
                return;
            }
        }
        
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            $user = Auth::user();
            
            // Only delete photos that are being replaced
            $existingPhotoUrls = array_filter($this->photos, function($photo) {
                return is_string($photo) && filter_var($photo, FILTER_VALIDATE_URL);
            });
            
            // Keep existing photos that are still in the array
            $photosToKeep = $user->photos()->whereIn('original_url', $existingPhotoUrls)->get();
            
            // Delete photos that were removed
            $user->photos()->whereNotIn('original_url', $existingPhotoUrls)->delete();
            
            $mediaUploadService = app(MediaUploadService::class);
            
            foreach ($this->photos as $index => $photo) {
                // Handle existing photos
                if (is_string($photo) && filter_var($photo, FILTER_VALIDATE_URL)) {
                    // Update the existing photo's properties
                    $existingPhoto = $user->photos()->where('original_url', $photo)->first();
                    if ($existingPhoto) {
                        $existingPhoto->update([
                            'is_profile_photo' => ($index === $this->profilePhotoIndex),
                            'order' => $index,
                            'is_private' => $this->isPrivate,
                        ]);
                    }
                    continue;
                }
                
                if ($photo) {
                    $isProfilePhoto = ($index === $this->profilePhotoIndex);
                    
                    try {
                        $uploadResult = $mediaUploadService->uploadMedia(
                            $photo,
                            'profile_photos',
                            $user->id,
                            ['is_profile_photo' => $isProfilePhoto]
                        );

                        \App\Models\UserPhoto::create([
                            'user_id' => $user->id,
                            'original_url' => $uploadResult['original_url'],
                            'thumbnail_url' => $uploadResult['thumbnail_url'],
                            'medium_url' => $uploadResult['medium_url'],
                            'is_profile_photo' => $isProfilePhoto,
                            'order' => $index,
                            'is_private' => $this->isPrivate,
                            'is_verified' => false,
                            'status' => 'approved', // Auto-approve for now
                            'uploaded_at' => now(),
                        ]);

                    } catch (\Exception $e) {
                        Log::error('Photo upload failed', [
                            'user_id' => $user->id,
                            'photo_index' => $index,
                            'error' => $e->getMessage()
                        ]);
                        throw new \Exception('Failed to upload photo ' . ($index + 1));
                    }
                }
            }
            
            // Redirect to next step
            return redirect()->route('onboard.location')
                ->with('success', 'Photos uploaded successfully! Now set your location.');
                
        } catch (\Exception $e) {
            Log::error('Photos submission failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = $e->getMessage() ?: 'An error occurred while uploading your photos. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function getCanContinueProperty()
    {
        return count($this->photos) >= 1;
    }

    public function render()
    {
        return view('livewire.profile.photos')
            ->layout('components.layouts.onboarding', [
                'title' => 'Your Photos - YorYor',
                'currentStep' => 6,
                'totalSteps' => 8
            ]);
    }
}