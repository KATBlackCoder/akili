<x-app-layout>
    <x-slot name="title">{{ $assignment->form->title }}</x-slot>

    @php
        $sections = $assignment->form->sections;
        $totalSections = $sections->count();
    @endphp

    <div
        x-data="{
            currentSection: 0,
            totalSections: {{ $totalSections }},
            progress() { return Math.round(((this.currentSection + 1) / this.totalSections) * 100); },
            next() { if (this.currentSection < this.totalSections - 1) this.currentSection++; },
            prev() { if (this.currentSection > 0) this.currentSection--; }
        }"
        class="max-w-2xl mx-auto space-y-6"
    >
        {{-- En-tête --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body pb-4">
                <h1 class="text-xl font-bold">{{ $assignment->form->title }}</h1>
                @if($assignment->form->description)
                <p class="text-sm text-base-content/60">{{ $assignment->form->description }}</p>
                @endif
                @if($assignment->due_at)
                <p class="text-sm {{ $assignment->isDueSoon() ? 'text-error' : 'text-base-content/60' }}">
                    Échéance : {{ $assignment->due_at->format('d/m/Y H:i') }}
                </p>
                @endif

                {{-- Progression --}}
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-base-content/60 mb-1">
                        <span x-text="'Section ' + (currentSection + 1) + '/' + totalSections"></span>
                        <span x-text="progress() + '% complété'"></span>
                    </div>
                    <progress class="progress progress-primary w-full" x-bind:value="progress()" max="100"></progress>
                </div>
            </div>
        </div>

        {{-- Formulaire --}}
        <form method="POST" action="{{ route('assignments.submit', $assignment) }}" enctype="multipart/form-data" id="submission-form">
            @csrf

            @foreach($sections as $sIndex => $section)
            <div x-show="currentSection === {{ $sIndex }}" class="space-y-4">
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="text-lg font-semibold mb-4">{{ $section->title }}</h2>
                        <div class="space-y-5">
                            @foreach($section->fields as $field)
                                @if(in_array($field->type, ['text', 'textarea']))
                                    <x-field-text :field="$field" />
                                @elseif(in_array($field->type, ['select', 'radio', 'checkbox']))
                                    <x-field-select :field="$field" />
                                @elseif($field->type === 'date')
                                    <x-field-date :field="$field" />
                                @elseif($field->type === 'number')
                                    <x-field-number :field="$field" />
                                @elseif($field->type === 'file')
                                    <x-field-file :field="$field" />
                                @elseif($field->type === 'rating')
                                    <x-field-rating :field="$field" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Navigation entre sections --}}
                <div class="flex justify-between">
                    <button
                        type="button"
                        class="btn btn-ghost"
                        x-show="currentSection > 0"
                        x-on:click="prev()"
                    >
                        ← Précédent
                    </button>
                    <div x-show="currentSection === 0"></div>

                    @if($sIndex < $totalSections - 1)
                    <button
                        type="button"
                        class="btn btn-primary"
                        x-show="currentSection === {{ $sIndex }}"
                        x-on:click="next()"
                    >
                        Suivant →
                    </button>
                    @else
                    <button
                        type="button"
                        class="btn btn-success"
                        x-show="currentSection === {{ $sIndex }}"
                        x-on:click="document.getElementById('confirm-submit').showModal()"
                    >
                        ✓ Soumettre
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </form>

        {{-- Modal de confirmation --}}
        <dialog id="confirm-submit" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Confirmer la soumission</h3>
                <p class="py-4">Une fois soumis, vous ne pourrez plus modifier votre réponse. Êtes-vous sûr ?</p>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn btn-ghost">Annuler</button>
                    </form>
                    <button
                        class="btn btn-success"
                        x-on:click="document.getElementById('submission-form').submit()"
                    >
                        Confirmer et soumettre
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>Fermer</button></form>
        </dialog>
    </div>
</x-app-layout>
