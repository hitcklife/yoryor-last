<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UserFamilyPreference;

class FamilyMarriage extends Component
{
    public $marriage_intention;
    public $children_preference;
    public $current_children;
    public $family_values = [];
    public $living_situation;
    public $family_involvement;
    public $marriage_timeline;
    public $family_importance;
    public $family_approval_important = false;
    public $previous_marriages = 0;
    public $homemaker_preference;

    public function mount()
    {
        $user = Auth::user();
        $familyPreference = $user->familyPreference;

        if ($familyPreference) {
            $this->marriage_intention = $familyPreference->marriage_intention;
            $this->children_preference = $familyPreference->children_preference;
            $this->current_children = $familyPreference->current_children;
            $this->family_values = $familyPreference->family_values ?: [];
            $this->living_situation = $familyPreference->living_situation;
            $this->family_involvement = $familyPreference->family_involvement;
            $this->marriage_timeline = $familyPreference->marriage_timeline;
            $this->family_importance = $familyPreference->family_importance;
            $this->family_approval_important = (bool) $familyPreference->family_approval_important;
            $this->previous_marriages = $familyPreference->previous_marriages ?: 0;
            $this->homemaker_preference = $familyPreference->homemaker_preference;
        }
    }

    public function toggleFamilyValue($value)
    {
        if (in_array($value, $this->family_values)) {
            $this->family_values = array_values(array_filter($this->family_values, function($v) use ($value) {
                return $v !== $value;
            }));
        } else {
            $this->family_values[] = $value;
        }
    }

    public function save()
    {
        $user = Auth::user();
        
        UserFamilyPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'marriage_intention' => $this->marriage_intention,
                'children_preference' => $this->children_preference,
                'current_children' => $this->current_children,
                'family_values' => $this->family_values,
                'living_situation' => $this->living_situation,
                'family_involvement' => $this->family_involvement,
                'marriage_timeline' => $this->marriage_timeline,
                'family_importance' => $this->family_importance,
                'family_approval_important' => $this->family_approval_important,
                'previous_marriages' => $this->previous_marriages,
                'homemaker_preference' => $this->homemaker_preference,
            ]
        );

        session()->flash('message', 'Family preferences saved successfully!');
        return redirect()->route('profile.enhance');
    }

    public function render()
    {
        return view('livewire.profile.family-marriage')
            ->layout('components.layouts.user', ['title' => 'Family & Marriage']);
    }
}
