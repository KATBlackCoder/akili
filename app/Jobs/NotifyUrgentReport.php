<?php

namespace App\Jobs;

use App\Models\Submission;
use App\Models\User;
use App\Notifications\UrgentReportNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUrgentReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly Submission $submission) {}

    public function handle(): void
    {
        $submission = $this->submission->load('submittedBy.supervisor.manager');
        $user = $submission->submittedBy;

        $recipients = collect();

        if ($user->role === 'employe' && $user->supervisor) {
            $recipients->push($user->supervisor);
        }

        $manager = match ($user->role) {
            'employe' => $user->supervisor?->manager,
            'superviseur' => $user->manager,
            default => null,
        };

        if ($manager) {
            $recipients->push($manager);
        }

        $recipients->unique('id')->each(function (User $recipient) use ($submission) {
            $recipient->notify(new UrgentReportNotification($submission));
        });
    }
}
