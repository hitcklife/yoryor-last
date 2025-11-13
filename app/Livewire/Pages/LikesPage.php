<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class LikesPage extends Component
{
    public function render()
    {
        return view('livewire.pages.likes-page')
            ->layout('layouts.app', ['title' => 'Likes']);
    }
}
