<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationToast extends Component
{
    public $show = false;
    public $type = 'success';
    public $title = '';
    public $message = '';
    public $icon = '';
    public $color = '';

    protected $listeners = ['showNotification' => 'displayNotification'];

    public function displayNotification($data)
    {
        $this->type = $data['type'] ?? 'success';
        $this->title = $data['title'] ?? '';
        $this->message = $data['message'] ?? '';

        switch ($this->type) {
            case 'success':
                $this->color = 'bg-emerald-500'; 
                break;
            case 'error':
                $this->color = 'bg-red-600';  
                break;
            case 'warning':
                $this->color = 'bg-orange-500';
                break;
            case 'info':
                $this->color = 'bg-blue-600'; 
                break;
        }

        $this->show = true;
    }

    public function hideNotification()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.notification-toast');
    }
}