<x-app-layout>
    <x-slot name="title">{{ $user->full_name }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('employees.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
            <h1 class="text-2xl font-bold flex-1">{{ $user->full_name }}</h1>
            <x-badge-status :status="$user->is_active ? 'active' : 'inactive'" />
        </div>

        {{-- Profil --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-start gap-6">
                    <div class="bg-primary text-primary-content rounded-xl w-20 h-20 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                        <span>{{ substr($user->firstname, 0, 1) }}{{ substr($user->lastname, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-base-content/50">Identifiant</div>
                            <div class="font-mono text-sm">{{ $user->username }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/50">Téléphone</div>
                            <div>{{ $user->phone }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/50">Département</div>
                            <div>{{ $user->department ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/50">Poste</div>
                            <div>{{ $user->job_title ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/50">Manager</div>
                            <div>{{ $user->manager?->full_name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/50">Date d'embauche</div>
                            <div>{{ $user->hired_at?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-actions justify-end mt-4">
                    @if(auth()->id() === $user->id || auth()->user()->hasRole('manager') || auth()->user()->hasRole('super-admin'))
                    <a href="{{ route('employees.edit', $user) }}" class="btn btn-outline btn-sm">Modifier</a>
                    @endif
                    @if(auth()->user()->hasRole('super-admin') && $user->hasRole('manager'))
                    <a href="{{ route('managers.privileges.edit', $user) }}" class="btn btn-outline btn-sm">Gérer les privilèges</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Assignations récentes --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="font-semibold mb-4">Questionnaires assignés</h3>
                @if($user->assignments->isEmpty())
                <p class="text-base-content/50 text-sm">Aucun questionnaire assigné</p>
                @else
                <div class="space-y-2">
                    @foreach($user->assignments->take(5) as $assignment)
                    <div class="flex items-center justify-between py-2 border-b border-base-200 last:border-0">
                        <div>
                            <div class="font-medium text-sm">{{ $assignment->form->title }}</div>
                            <div class="text-xs text-base-content/50">{{ $assignment->created_at->format('d/m/Y') }}</div>
                        </div>
                        <x-badge-status :status="$assignment->status" />
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
