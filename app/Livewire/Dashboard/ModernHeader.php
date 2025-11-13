<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class ModernHeader extends Component
{
    public $unreadNotifications = 0;
    public $currentRoute;
    
    public function mount()
    {
        $this->currentRoute = request()->route()->getName();
        $this->unreadNotifications = auth()->user()->unreadNotifications()->count() ?? 0;
    }
    
    public function logout()
    {
        auth()->logout();
        return redirect()->route('home');
    }
    
    public function render()
    {
        return view('livewire.dashboard.modern-header');
    }
}
