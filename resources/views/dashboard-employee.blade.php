<x-app-layout>
    <x-slot name="title">Tableau de bord</x-slot>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold">Bonjour, {{ auth()->user()->firstname }} 👋</h1>
            <p class="text-base-content/60">Voici vos questionnaires assignés</p>
        </div>

        @forelse($myAssignments as $assignment)
        <div class="card bg-base-100 shadow hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-lg truncate">{{ $assignment->form->title }}</h3>
                        <p class="text-sm text-base-content/60">Assigné par {{ $assignment->assignedBy->full_name }}</p>
                        @if($assignment->due_at)
                        <p class="text-sm mt-1 {{ $assignment->isDueSoon() ? 'text-error font-medium' : 'text-base-content/60' }}">
                            Échéance : {{ $assignment->due_at->format('d/m/Y H:i') }}
                            @if($assignment->isDueSoon())
                                <span class="badge badge-error badge-xs ml-1">Urgent</span>
                            @endif
                        </p>
                        @endif
                    </div>
                    <x-badge-status :status="$assignment->status" />
                </div>
                <div class="card-actions justify-end mt-2">
                    @if($assignment->status === 'pending')
                    <a href="{{ route('assignments.fill', $assignment) }}" class="btn btn-primary btn-sm">
                        Remplir
                    </a>
                    @elseif($assignment->submission)
                    <a href="{{ route('submissions.show', $assignment->submission) }}" class="btn btn-ghost btn-sm">
                        Voir ma réponse
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="card bg-base-100 shadow">
            <div class="card-body items-center text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                <p class="text-base-content/50 mt-4">Aucun questionnaire assigné pour l'instant</p>
            </div>
        </div>
        @endforelse
    </div>
</x-app-layout>
