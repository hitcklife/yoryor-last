<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCareerProfile;

class CareerEducation extends Component
{
    public $education_level;
    public $field_of_study;
    public $work_status;
    public $occupation;
    public $employer;
    public $career_goals;
    public $income_range;

    public function mount()
    {
        $user = Auth::user();
        $careerProfile = $user->careerProfile;

        if ($careerProfile) {
            $this->education_level = $careerProfile->education_level;
            $this->field_of_study = $careerProfile->field_of_study;
            $this->work_status = $careerProfile->work_status;
            $this->occupation = $careerProfile->occupation;
            $this->employer = $careerProfile->employer;
            $this->career_goals = $careerProfile->career_goals;
            $this->income_range = $careerProfile->income_range;
        }
    }

    public function save()
    {
        $user = Auth::user();
        
        UserCareerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'education_level' => $this->education_level,
                'field_of_study' => $this->field_of_study,
                'work_status' => $this->work_status,
                'occupation' => $this->occupation,
                'employer' => $this->employer,
                'career_goals' => $this->career_goals,
                'income_range' => $this->income_range,
            ]
        );

        session()->flash('message', 'Career information saved successfully!');
        return redirect()->route('profile.enhance');
    }

    public function render()
    {
        return view('livewire.profile.career-education')
            ->layout('components.layouts.user', ['title' => 'Career & Education']);
    }
}
