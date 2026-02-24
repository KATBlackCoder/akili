<x-app-layout>
    <x-slot name="title">{{ $form->title }}</x-slot>

    <div x-data="reportType1({
            formId: {{ $form->id }},
            formAssignmentId: {{ $formAssignment->id }},
            userId: {{ auth()->id() }},
            initialDraft: {!! $draft ? json_encode($draft->draft_data) : '{}' !!}
         })"
         x-init="init()">

        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">← Retour</a>
            <div class="flex-1">
                <h1 class="text-xl font-bold">{{ $form->title }}</h1>
                <p class="text-sm text-base-content/60">Rapport journalier</p>
            </div>
            <div id="draft-badge"
                 hx-post="{{ route('reports.type1.draft', $form) }}"
                 hx-trigger="every 30s"
                 hx-vals="js:{draft_data: JSON.stringify(getDraftData()), form_assignment_id: {{ $formAssignment->id }}}"
                 hx-target="#draft-badge"
                 hx-swap="outerHTML">
                <span class="badge badge-ghost text-xs">Non sauvegardé</span>
            </div>
        </div>

        @if($form->description)
        <div class="alert alert-info mb-4">
            <span class="text-sm">{{ $form->description }}</span>
        </div>
        @endif

        <input type="hidden" name="current_row_count" x-bind:value="rowCount" id="current_row_count" />

        <div class="card bg-base-100 shadow mb-4">
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead>
                        <tr class="text-xs uppercase text-base-content/40">
                            <th class="w-10">#</th>
                            @foreach($form->sections->flatMap->fields->sortBy('order') as $field)
                            <th>{{ $field->label }}{{ $field->is_required ? ' *' : '' }}</th>
                            @endforeach
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="rows-container">
                        {{-- Les lignes sont ajoutées par HTMX --}}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between gap-4 mt-4">
            <button
                hx-post="{{ route('reports.type1.rows.add', $form) }}"
                hx-include="#current_row_count"
                hx-target="#rows-container"
                hx-swap="beforeend"
                x-on:htmx:after-request="rowCount++; saveToLocalStorage()"
                class="btn btn-outline btn-sm">
                + Ajouter une ligne
            </button>

            <button x-on:click="confirmSubmit()" class="btn btn-success">
                Soumettre le rapport
            </button>
        </div>

        {{-- Modal de confirmation --}}
        <dialog id="confirm-submit-modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Confirmer la soumission</h3>
                <p class="py-4 text-sm text-base-content/70">
                    Une fois soumis, vous ne pourrez plus modifier ce rapport journalier.
                </p>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn btn-ghost">Annuler</button>
                    </form>
                    <form method="POST" action="{{ route('reports.type1.submit', $form) }}" id="submit-form">
                        @csrf
                        <input type="hidden" name="form_assignment_id" value="{{ $formAssignment->id }}" />
                        <div id="hidden-rows-container"></div>
                        <button type="submit" class="btn btn-success">Soumettre</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>Fermer</button></form>
        </dialog>
    </div>
</x-app-layout>
