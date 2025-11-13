<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCulturalProfile;

class CulturalBackground extends Component
{
    // Heritage & Beliefs
    public $ethnicity;
    public $religion;
    public $religiousness_level;

    // Languages - using existing fields
    public $native_languages = [];
    public $spoken_languages = [];
    public $preferred_communication_language;

    // Religious Practice Advanced
    public $observes_ramadan = false;
    public $halal_lifestyle = false;
    public $prefers_halal_dates = false;
    public $mosque_attendance;
    public $quran_reading;

    // Cultural Depth - using existing fields
    public $cultural_events_participation;
    public $traditional_clothing_comfort;
    public $uzbek_cuisine_knowledge;
    public $gender_role_views;
    public $lifestyle_type;
    public $uzbek_region;

    public function mount()
    {
        $user = Auth::user();
        $culturalProfile = $user->culturalProfile;

        if ($culturalProfile) {
            $this->ethnicity = $culturalProfile->ethnicity;
            $this->religion = $culturalProfile->religion;
            $this->religiousness_level = $culturalProfile->religiousness_level;
            $this->native_languages = $culturalProfile->native_languages ?: [];
            $this->spoken_languages = $culturalProfile->spoken_languages ?: [];
            $this->preferred_communication_language = $culturalProfile->preferred_communication_language;
            $this->observes_ramadan = (bool) $culturalProfile->observes_ramadan;
            $this->halal_lifestyle = (bool) $culturalProfile->halal_lifestyle;
            $this->prefers_halal_dates = (bool) $culturalProfile->prefers_halal_dates;
            $this->mosque_attendance = $culturalProfile->mosque_attendance;
            $this->quran_reading = $culturalProfile->quran_reading;
            $this->cultural_events_participation = $culturalProfile->cultural_events_participation;
            $this->traditional_clothing_comfort = $culturalProfile->traditional_clothing_comfort;
            $this->uzbek_cuisine_knowledge = $culturalProfile->uzbek_cuisine_knowledge;
            $this->gender_role_views = $culturalProfile->gender_role_views;
            $this->lifestyle_type = $culturalProfile->lifestyle_type;
            $this->uzbek_region = $culturalProfile->uzbek_region;
        }
    }

    public function toggleSpokenLanguage($language)
    {
        if (in_array($language, $this->spoken_languages)) {
            $this->spoken_languages = array_values(array_filter($this->spoken_languages, function($l) use ($language) {
                return $l !== $language;
            }));
        } else {
            $this->spoken_languages[] = $language;
        }
    }

    public function toggleNativeLanguage($language)
    {
        if (in_array($language, $this->native_languages)) {
            $this->native_languages = array_values(array_filter($this->native_languages, function($l) use ($language) {
                return $l !== $language;
            }));
        } else {
            $this->native_languages[] = $language;
        }
    }

    public function save()
    {
        $user = Auth::user();
        
        UserCulturalProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'ethnicity' => $this->ethnicity,
                'religion' => $this->religion,
                'religiousness_level' => $this->religiousness_level,
                'native_languages' => $this->native_languages,
                'spoken_languages' => $this->spoken_languages,
                'preferred_communication_language' => $this->preferred_communication_language,
                'observes_ramadan' => $this->observes_ramadan,
                'halal_lifestyle' => $this->halal_lifestyle,
                'prefers_halal_dates' => $this->prefers_halal_dates,
                'mosque_attendance' => $this->mosque_attendance,
                'quran_reading' => $this->quran_reading,
                'cultural_events_participation' => $this->cultural_events_participation,
                'traditional_clothing_comfort' => $this->traditional_clothing_comfort,
                'uzbek_cuisine_knowledge' => $this->uzbek_cuisine_knowledge,
                'gender_role_views' => $this->gender_role_views,
                'lifestyle_type' => $this->lifestyle_type,
                'uzbek_region' => $this->uzbek_region,
            ]
        );

        session()->flash('message', 'Cultural background saved successfully!');
        return redirect()->route('profile.enhance');
    }

    public function render()
    {
        return view('livewire.profile.cultural-background')
            ->layout('components.layouts.user', ['title' => 'Cultural Background']);
    }
}
