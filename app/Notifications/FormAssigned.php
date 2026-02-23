<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FormAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Assignment $assignment) {}

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
            'message' => "Le questionnaire \"{$this->assignment->form->title}\" vous a été assigné.",
            'url' => route('assignments.fill', $this->assignment),
            'due_at' => $this->assignment->due_at?->toISOString(),
        ];
    }
}
