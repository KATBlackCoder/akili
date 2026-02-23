<?php

namespace App\Notifications;

use App\Models\Submission;
use App\Models\SubmissionCorrection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubmissionReturned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Submission $submission,
        public readonly SubmissionCorrection $correction
    ) {}

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
            'title' => 'Soumission renvoyée en correction',
            'message' => "Votre soumission pour \"{$this->submission->assignment->form->title}\" a été renvoyée en correction.",
            'message_from_manager' => $this->correction->message,
            'url' => route('submissions.correct', $this->submission),
        ];
    }
}
