<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leaveRequest) {}

    /**
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->leaveRequest->status === 'approved' ? 'approuvée' : 'refusée';

        return [
            'title' => "Demande de congé {$status}",
            'message' => "Votre demande de congé du {$this->leaveRequest->start_date->format('d/m/Y')} au {$this->leaveRequest->end_date->format('d/m/Y')} a été {$status}.",
            'url' => route('leave-requests.index'),
        ];
    }
}
