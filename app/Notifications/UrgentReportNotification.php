<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UrgentReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Submission $submission) {}

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
        $submitter = $this->submission->submittedBy;

        return [
            'title' => '⚠️ Rapport urgent soumis',
            'message' => "{$submitter->full_name} a soumis un rapport urgent : \"{$this->submission->form->title}\".",
            'url' => route('submissions.show', $this->submission),
            'submitted_at' => $this->submission->submitted_at?->toISOString(),
            'submitter_id' => $submitter->id,
        ];
    }
}
