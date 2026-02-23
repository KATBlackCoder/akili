<x-app-layout>
    <x-slot name="title">Soumission</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('assignments.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
            <div class="flex-1">
                <h1 class="text-xl font-bold">{{ $submission->assignment->form->title }}</h1>
                <p class="text-sm text-base-content/60">
                    Soumis par {{ $submission->submitter->full_name }} le {{ $submission->submitted_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <x-badge-status :status="$submission->status" />
        </div>

        {{-- Message correction si renvoyé --}}
        @if($submission->status === 'returned')
        @php $activeCorrection = $submission->corrections()->where('status', 'pending')->latest()->first(); @endphp
        @if($activeCorrection)
        <div class="alert alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <div>
                <div class="font-bold">Cette soumission a été renvoyée en correction</div>
                @if($activeCorrection->message)
                    <div class="text-sm mt-1">Message : {{ $activeCorrection->message }}</div>
                @endif
                @if(auth()->id() === $submission->submitted_by)
                <a href="{{ route('submissions.correct', $submission) }}" class="btn btn-warning btn-sm mt-2">Corriger</a>
                @endif
            </div>
        </div>
        @endif
        @endif

        {{-- Réponses par section --}}
        @php $answersByFieldId = $submission->answers->keyBy('field_id'); @endphp

        @foreach($submission->assignment->form->sections as $section)
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="font-semibold text-lg mb-4">{{ $section->title }}</h2>
                <div class="space-y-4">
                    @foreach($section->fields as $field)
                    @php $answer = $answersByFieldId->get($field->id); @endphp
                    <div class="border-b border-base-200 pb-3 last:border-0">
                        <div class="text-sm font-medium text-base-content/70">{{ $field->label }}</div>
                        <div class="mt-1">
                            @if($answer && $answer->file_path)
                                <a href="{{ asset('storage/'.$answer->file_path) }}" target="_blank" class="link link-primary text-sm">📎 Voir le fichier</a>
                            @elseif($answer && $answer->value)
                                @if($field->type === 'rating')
                                    <div class="flex gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= (int)$answer->value ? 'text-warning' : 'text-base-content/20' }}">★</span>
                                        @endfor
                                        <span class="text-sm ml-1">({{ $answer->value }}/5)</span>
                                    </div>
                                @else
                                    <div class="text-sm">{{ $answer->value }}</div>
                                @endif
                            @else
                                <span class="text-sm text-base-content/40 italic">— Non renseigné</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        {{-- Actions manager --}}
        @if(auth()->user()->hasRole('manager') || auth()->user()->hasRole('super-admin'))
        @if($submission->status !== 'returned')
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="font-semibold mb-4">Actions</h3>
                <button class="btn btn-warning" x-on:click="document.getElementById('return-modal').showModal()">
                    Renvoyer en correction
                </button>
            </div>
        </div>

        {{-- Modal renvoi en correction --}}
        <dialog id="return-modal" class="modal">
            <div class="modal-box max-w-2xl">
                <h3 class="font-bold text-lg mb-4">Renvoyer en correction</h3>
                <form method="POST" action="{{ route('submissions.return', $submission) }}">
                    @csrf
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text font-medium">Portée de la correction</span></label>
                        <div class="flex gap-4">
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="scope" value="full" class="radio radio-primary" checked />
                                <span class="label-text">Toute la soumission</span>
                            </label>
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="scope" value="partial" class="radio radio-warning" />
                                <span class="label-text">Champs spécifiques</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text font-medium">Message pour l'employé (optionnel)</span></label>
                        <textarea name="message" class="textarea textarea-bordered" rows="3" placeholder="Expliquez ce qui doit être corrigé..."></textarea>
                    </div>
                    <div class="modal-action">
                        <form method="dialog"><button class="btn btn-ghost">Annuler</button></form>
                        <button type="submit" class="btn btn-warning">Renvoyer</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop"><button>Fermer</button></form>
        </dialog>
        @endif
        @endif
    </div>
</x-app-layout>
