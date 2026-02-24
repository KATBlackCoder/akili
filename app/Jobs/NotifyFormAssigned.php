<?php

namespace App\Jobs;

use App\Models\FormAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyFormAssigned implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly FormAssignment $formAssignment) {}

    public function handle(): void
    {
        $assignment = $this->formAssignment->load('form', 'selectedUsers');
        $recipients = $assignment->resolveRecipients();

        $recipients->each(function (User $recipient) use ($assignment) {
            $recipient->notify(new \App\Notifications\FormAssignedNew($assignment));
        });
    }
}
