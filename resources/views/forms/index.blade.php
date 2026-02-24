<x-app-layout>
    <x-slot name="title">Formulaires</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Mes formulaires</h1>
                <p class="text-base-content/60">{{ $forms->total() }} questionnaire(s)</p>
            </div>
            @if(auth()->user()->canCreateForms())
            <a href="{{ route('forms.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Nouveau formulaire
            </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($forms as $form)
            <div class="card bg-base-100 shadow hover:shadow-md transition-shadow">
                <div class="card-body">
                    <div class="flex justify-between items-start gap-2">
                        <h3 class="card-title text-base line-clamp-2 flex-1">{{ $form->title }}</h3>
                        <div class="flex flex-col items-end gap-1">
                            @if($form->report_type === 'type1')
                            <span class="badge badge-info badge-sm">Journalier</span>
                            @elseif($form->report_type === 'type2')
                            <span class="badge badge-error badge-sm">Urgent</span>
                            @endif
                            <x-badge-status :status="$form->is_active ? 'active' : 'inactive'" />
                        </div>
                    </div>
                    @if($form->description)
                    <p class="text-sm text-base-content/60 line-clamp-2">{{ $form->description }}</p>
                    @endif
                    <div class="text-xs text-base-content/50 mt-2">
                        Créé par {{ $form->creator->full_name }} · {{ $form->created_at->format('d/m/Y') }}
                    </div>
                    <div class="card-actions justify-between items-center mt-2">
                        <div class="flex gap-2 flex-wrap">
                            <a href="{{ route('forms.show', $form) }}" class="btn btn-ghost btn-xs">Voir</a>
                            <a href="{{ route('forms.edit', $form) }}" class="btn btn-ghost btn-xs">Modifier</a>
                            @if(auth()->user()->canCreateForms())
                            <button onclick="document.getElementById('assign-modal-{{ $form->id }}').showModal()"
                                    class="btn btn-outline btn-xs btn-primary">
                                Assigner
                            </button>
                            @endif
                        </div>
                        <div class="flex gap-1">
                            <a href="{{ route('forms.export.pdf', $form) }}" class="btn btn-ghost btn-xs tooltip" data-tip="PDF">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            </a>
                            <a href="{{ route('forms.export.excel', $form) }}" class="btn btn-ghost btn-xs tooltip" data-tip="Excel">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Modal d'assignation --}}
                    @if(auth()->user()->canCreateForms())
                    <dialog id="assign-modal-{{ $form->id }}" class="modal">
                        <div class="modal-box w-11/12 max-w-2xl" x-data="assignForm()">
                            <h3 class="font-bold text-lg mb-4">Assigner — {{ $form->title }}</h3>

                            <form method="POST" action="{{ route('forms.assign', $form) }}" class="space-y-4">
                                @csrf

                                {{-- Mode d'assignation --}}
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Mode d'assignation</span>
                                    </label>
                                    <div class="flex flex-wrap gap-3">
                                        <label class="label cursor-pointer gap-2 border rounded-lg px-3 py-2
                                                      hover:bg-base-200 has-[:checked]:border-primary">
                                            <input type="radio" name="scope_type" value="role"
                                                   class="radio radio-primary radio-sm" x-model="scopeType" />
                                            <span class="label-text">Global par type</span>
                                        </label>
                                        <label class="label cursor-pointer gap-2 border rounded-lg px-3 py-2
                                                      hover:bg-base-200 has-[:checked]:border-primary">
                                            <input type="radio" name="scope_type" value="individual"
                                                   class="radio radio-primary radio-sm" x-model="scopeType" />
                                            <span class="label-text">Sélection individuelle</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- MODE GLOBAL --}}
                                <div x-show="scopeType === 'role'" x-cloak class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Assigner à</span>
                                    </label>
                                    <div class="flex flex-col gap-2">
                                        <label class="label cursor-pointer justify-start gap-3 border rounded-lg px-3 py-2
                                                      hover:bg-base-200 has-[:checked]:border-primary">
                                            <input type="radio" name="scope_role" value="employe"
                                                   class="radio radio-primary radio-sm" />
                                            <span class="label-text">Tous les Employés</span>
                                        </label>
                                        <label class="label cursor-pointer justify-start gap-3 border rounded-lg px-3 py-2
                                                      hover:bg-base-200 has-[:checked]:border-primary">
                                            <input type="radio" name="scope_role" value="superviseur"
                                                   class="radio radio-primary radio-sm" />
                                            <span class="label-text">Tous les Superviseurs</span>
                                        </label>
                                        <label class="label cursor-pointer justify-start gap-3 border rounded-lg px-3 py-2
                                                      hover:bg-base-200 has-[:checked]:border-primary">
                                            <input type="radio" name="scope_role" value="both"
                                                   class="radio radio-primary radio-sm" />
                                            <span class="label-text">
                                                Superviseurs <span class="badge badge-ghost badge-sm">+</span> Employés
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                {{-- MODE INDIVIDUEL --}}
                                <div x-show="scopeType === 'individual'" x-cloak class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Sélectionner les personnes</span>
                                        <span class="label-text-alt badge badge-primary badge-sm"
                                              x-text="selectedCount + ' sélectionné(s)'"></span>
                                    </label>

                                    <input type="text" placeholder="Rechercher par nom..."
                                           class="input input-bordered input-sm mb-2"
                                           x-model="search" />

                                    <div class="border rounded-lg overflow-y-auto max-h-56 p-2 space-y-1">

                                        @php $superviseursBranch = ($branchUsers ?? collect())->where('role', 'superviseur'); @endphp
                                        @php $employesBranch = ($branchUsers ?? collect())->where('role', 'employe'); @endphp

                                        @forelse($superviseursBranch as $u)
                                        <label class="label cursor-pointer justify-start gap-3
                                                      hover:bg-base-200 rounded px-2 py-1"
                                               x-show="matchSearch('{{ strtolower($u->firstname . ' ' . $u->lastname) }}')">
                                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                                                   class="checkbox checkbox-primary checkbox-sm"
                                                   x-on:change="updateCount()" />
                                            <span class="label-text font-medium">{{ $u->firstname }} {{ $u->lastname }}</span>
                                            <span class="badge badge-warning badge-sm ml-auto">Superviseur</span>
                                        </label>
                                        @empty
                                        @endforelse

                                        @if($superviseursBranch->isNotEmpty() && $employesBranch->isNotEmpty())
                                        <div class="divider my-1 text-xs">Employés</div>
                                        @endif

                                        @foreach($employesBranch as $u)
                                        <label class="label cursor-pointer justify-start gap-3
                                                      hover:bg-base-200 rounded px-2 py-1"
                                               x-show="matchSearch('{{ strtolower($u->firstname . ' ' . $u->lastname) }}')">
                                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                                                   class="checkbox checkbox-primary checkbox-sm"
                                                   x-on:change="updateCount()" />
                                            <span class="label-text font-medium">{{ $u->firstname }} {{ $u->lastname }}</span>
                                            <span class="badge badge-info badge-sm ml-auto">Employé</span>
                                        </label>
                                        @endforeach

                                    </div>
                                </div>

                                {{-- Échéance (Type 1 uniquement) --}}
                                @if($form->report_type === 'type1')
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">Date d'échéance</span>
                                        <span class="label-text-alt">Optionnel</span>
                                    </label>
                                    <input type="datetime-local" name="due_at" class="input input-bordered w-full" />
                                </div>
                                @endif

                                <div class="modal-action">
                                    <button type="button" onclick="this.closest('dialog').close()"
                                            class="btn btn-ghost">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Assigner</button>
                                </div>
                            </form>
                        </div>
                        <form method="dialog" class="modal-backdrop"><button>Fermer</button></form>
                    </dialog>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-3 card bg-base-100 shadow">
                <div class="card-body items-center text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <p class="text-base-content/50 mt-4">Aucun formulaire créé</p>
                    @if(auth()->user()->canCreateForms())
                    <a href="{{ route('forms.create') }}" class="btn btn-primary btn-sm mt-4">Créer mon premier formulaire</a>
                    @endif
                </div>
            </div>
            @endforelse
        </div>

        {{ $forms->links() }}
    </div>

    <script>
    function assignForm() {
        return {
            scopeType: 'role',
            search: '',
            selectedCount: 0,
            matchSearch(name) {
                return name.includes(this.search.toLowerCase());
            },
            updateCount() {
                this.selectedCount = document.querySelectorAll(
                    'input[name="user_ids[]"]:checked'
                ).length;
            }
        }
    }
    </script>
</x-app-layout>
