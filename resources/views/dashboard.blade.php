<x-app-layout>
    <x-slot name="title">Tableau de bord</x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold">Tableau de bord</h1>
                <p class="text-base-content/60 mt-0.5">Bienvenue, {{ auth()->user()->full_name }}</p>
            </div>
            @if(!auth()->user()->hasRole('employe'))
            <a href="{{ route('forms.create') }}" class="btn btn-primary btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Nouveau formulaire
            </a>
            @endif
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            {{-- Taux de complétion --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-base-content/60">Taux de complétion</span>
                        <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-primary">{{ $completionRate }}%</div>
                    <div class="mt-2">
                        <div class="flex justify-between text-xs text-base-content/50 mb-1">
                            <span>{{ $completedAssignments }}/{{ $totalAssignments }} questionnaires</span>
                        </div>
                        <progress class="progress progress-primary h-1.5" value="{{ $completionRate }}" max="100"></progress>
                    </div>
                </div>
            </div>

            {{-- Soumissions en attente --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-base-content/60">En attente de révision</span>
                        <div class="w-10 h-10 bg-warning/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold {{ $pendingSubmissions > 0 ? 'text-warning' : 'text-base-content' }}">
                        {{ $pendingSubmissions }}
                    </div>
                    <div class="text-xs text-base-content/50 mt-2">
                        {{ $pendingSubmissions > 0 ? 'Soumissions à valider' : 'Aucune soumission en attente' }}
                    </div>
                </div>
            </div>

            {{-- Employés --}}
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-base-content/60">Équipe</span>
                        <div class="w-10 h-10 bg-info/10 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-info">{{ $employeeIds->count() }}</div>
                    <div class="text-xs text-base-content/50 mt-2">
                        {{ $activeForms }} formulaire(s) actif(s)
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Dernières soumissions --}}
            <div class="card bg-base-100 shadow-sm border border-base-200 lg:col-span-3">
                <div class="card-body p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-base">Dernières soumissions</h2>
                        <a href="{{ route('assignments.index') }}" class="text-xs text-primary hover:underline">Voir tout</a>
                    </div>
                    <div class="overflow-x-auto -mx-1">
                        <table class="table table-sm">
                            <thead>
                                <tr class="text-base-content/50 text-xs uppercase">
                                    <th>Employé</th>
                                    <th>Formulaire</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSubmissions as $submission)
                                <tr class="hover">
                                    <td class="font-medium text-sm">{{ $submission->submitter->full_name }}</td>
                                    <td class="max-w-[140px] truncate text-sm text-base-content/70">{{ $submission->assignment->form->title }}</td>
                                    <td><x-badge-status :status="$submission->status" /></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-8 text-base-content/40 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Aucune soumission
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Assignations en cours --}}
            <div class="card bg-base-100 shadow-sm border border-base-200 lg:col-span-2">
                <div class="card-body p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-base">Assignations en cours</h2>
                        <a href="{{ route('assignments.index') }}" class="text-xs text-primary hover:underline">Voir tout</a>
                    </div>
                    <div class="space-y-2">
                        @forelse($recentAssignments as $assignment)
                        <div class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-base-200 transition-colors">
                            <div class="bg-base-300 text-base-content rounded-full w-8 h-8 flex items-center justify-center text-xs font-medium flex-shrink-0">
                                <span>{{ substr($assignment->employee->firstname, 0, 1) }}{{ substr($assignment->employee->lastname, 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate">{{ $assignment->employee->full_name }}</div>
                                <div class="text-xs text-base-content/50 truncate">{{ $assignment->form->title }}</div>
                            </div>
                            <x-badge-status :status="$assignment->status" />
                        </div>
                        @empty
                        <div class="text-center py-8 text-base-content/40 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Aucune assignation en cours
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
