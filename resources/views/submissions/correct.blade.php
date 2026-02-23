<x-app-layout>
    <x-slot name="title">Correction</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <h1 class="text-xl font-bold">{{ $submission->assignment->form->title }}</h1>
            <div class="badge badge-warning badge-lg mt-2">Correction requise</div>
        </div>

        @if($activeCorrection?->message)
        <div class="alert alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <div>
                <div class="font-bold">Message du manager :</div>
                <div>{{ $activeCorrection->message }}</div>
            </div>
        </div>
        @endif

        @if($activeCorrection?->scope === 'partial')
        <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Seuls les champs non grisés peuvent être modifiés.</span>
        </div>
        @endif

        @php $answersByFieldId = $submission->answers->keyBy('field_id'); @endphp

        <form method="POST" action="{{ route('submissions.correct.update', $submission) }}" enctype="multipart/form-data">
            @csrf

            @foreach($submission->assignment->form->sections as $section)
            <div class="card bg-base-100 shadow mb-4">
                <div class="card-body">
                    <h2 class="font-semibold text-lg mb-4">{{ $section->title }}</h2>
                    <div class="space-y-4">
                        @foreach($section->fields as $field)
                        @php
                            $isLocked = in_array($field->id, $lockedFieldIds);
                            $answer = $answersByFieldId->get($field->id);
                            $value = $answer?->value;
                        @endphp
                        <div class="{{ $isLocked ? 'opacity-50' : '' }}">
                            @if($isLocked)
                                <div class="form-control w-full">
                                    <label class="label">
                                        <span class="label-text font-medium text-base-content/60">
                                            {{ $field->label }}
                                            <span class="badge badge-neutral badge-xs ml-1">Verrouillé</span>
                                        </span>
                                    </label>
                                    <div class="p-3 bg-base-200 rounded-lg text-sm text-base-content/70">
                                        {{ $value ?? '—' }}
                                    </div>
                                </div>
                            @elseif(in_array($field->type, ['text', 'textarea']))
                                <x-field-text :field="$field" :value="$value" />
                            @elseif(in_array($field->type, ['select', 'radio', 'checkbox']))
                                <x-field-select :field="$field" :value="$value" />
                            @elseif($field->type === 'date')
                                <x-field-date :field="$field" :value="$value" />
                            @elseif($field->type === 'number')
                                <x-field-number :field="$field" :value="$value" />
                            @elseif($field->type === 'file')
                                <x-field-file :field="$field" :value="$answer?->file_path" />
                            @elseif($field->type === 'rating')
                                <x-field-rating :field="$field" :value="$value" />
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach

            <div class="flex gap-3 justify-end">
                <a href="{{ route('submissions.show', $submission) }}" class="btn btn-ghost">Annuler</a>
                <button type="submit" class="btn btn-success">Soumettre la correction</button>
            </div>
        </form>
    </div>
</x-app-layout>
