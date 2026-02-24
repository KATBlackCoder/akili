<x-app-layout>
    <x-slot name="title">Assignations</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Assignations</h1>
                <p class="text-base-content/60">{{ $assignments->total() }} assignation(s)</p>
            </div>
            @if(!auth()->user()->hasRole('employe') && $forms->count() > 0)
            <button class="btn btn-primary" onclick="document.getElementById('assign-modal').showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Assigner
            </button>
            @endif
        </div>

        <div class="card bg-base-100 shadow overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="text-xs uppercase text-base-content/40">
                        <th>Formulaire</th>
                        @if(!auth()->user()->hasRole('employe'))<th class="hidden sm:table-cell">Employé</th>@endif
                        <th class="hidden sm:table-cell">Échéance</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    <tr class="hover">
                        <td>
                            <div class="font-medium text-sm">{{ $assignment->form->title }}</div>
                            @if(!auth()->user()->hasRole('employe'))
                            <div class="text-xs text-base-content/40 sm:hidden">{{ $assignment->employee->full_name }}</div>
                            @endif
                            @if($assignment->due_at)
                            <div class="text-xs sm:hidden {{ $assignment->isDueSoon() ? 'text-error font-medium' : 'text-base-content/40' }}">
                                {{ $assignment->due_at->format('d/m/Y') }}
                            </div>
                            @endif
                        </td>
                        @if(!auth()->user()->hasRole('employe'))
                        <td class="hidden sm:table-cell text-sm">{{ $assignment->employee->full_name }}</td>
                        @endif
                        <td class="hidden sm:table-cell">
                            @if($assignment->due_at)
                                <span class="text-sm {{ $assignment->isDueSoon() ? 'text-error font-medium' : '' }}">
                                    {{ $assignment->due_at->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-base-content/40">—</span>
                            @endif
                        </td>
                        <td><x-badge-status :status="$assignment->status" /></td>
                        <td>
                            @if($assignment->status === 'pending' && $assignment->assigned_to === auth()->id())
                                <a href="{{ route('assignments.fill', $assignment) }}" class="btn btn-primary btn-xs">Remplir</a>
                            @elseif($assignment->submission)
                                <a href="{{ route('submissions.show', $assignment->submission) }}" class="btn btn-ghost btn-xs">Voir</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-base-content/40 py-12 text-sm">Aucune assignation</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $assignments->links() }}
    </div>

    {{-- Modal assignation --}}
    @if(!auth()->user()->hasRole('employe'))
    <dialog id="assign-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Assigner un questionnaire</h3>
            <form method="POST" action="{{ route('assignments.store') }}">
                @csrf
                <div class="form-control w-full mb-4">
                    <label class="label"><span class="label-text font-medium">Formulaire *</span></label>
                    <select name="form_id" class="select select-bordered w-full" required>
                        <option value="">Choisir un formulaire...</option>
                        @foreach($forms as $form)
                        <option value="{{ $form->id }}">{{ $form->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control w-full mb-4">
                    <label class="label"><span class="label-text font-medium">Employé(s) *</span></label>
                    <select name="employee_ids[]" class="select select-bordered w-full" multiple required>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                    <label class="label"><span class="label-text-alt">Maintenez Ctrl pour sélectionner plusieurs</span></label>
                </div>
                <div class="form-control w-full mb-4">
                    <label class="label"><span class="label-text font-medium">Date d'échéance</span></label>
                    <input type="datetime-local" name="due_at" class="input input-bordered w-full" min="{{ now()->format('Y-m-d\TH:i') }}" />
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('assign-modal').close()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Assigner</button>
                </div>
            </form>
        </div>
        <button class="modal-backdrop" onclick="document.getElementById('assign-modal').close()"></button>
    </dialog>
    @endif
</x-app-layout>
