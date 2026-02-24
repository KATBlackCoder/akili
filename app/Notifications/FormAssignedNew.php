<?php

namespace App\Notifications;

use App\Models\FormAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FormAssignedNew extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly FormAssignment $formAssignment) {}

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
        return [
            'title' => 'Nouveau questionnaire assigné',
            'message' => "Le questionnaire \"{$this->formAssignment->form->title}\" vous a été assigné.",
            'due_at' => $this->formAssignment->due_at?->toISOString(),
        ];
    }
}
