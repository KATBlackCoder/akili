<x-app-layout>
    <x-slot name="title">{{ $form->title }} — Urgent</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">← Retour</a>
            <div class="flex-1">
                <h1 class="text-xl font-bold">{{ $form->title }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge badge-error badge-sm">Urgent</span>
                    <p class="text-sm text-base-content/60">Rapport urgent — soumission immédiate</p>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span class="text-sm">Ce rapport sera immédiatement transmis à votre superviseur et manager.</span>
        </div>

        @if($form->description)
        <div class="card bg-base-100 shadow mb-4">
            <div class="card-body py-3">
                <p class="text-sm text-base-content/70">{{ $form->description }}</p>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('reports.type2.submit', $form) }}">
            @csrf
            <input type="hidden" name="form_assignment_id" value="{{ $formAssignment->id }}" />

            <div class="space-y-4">
                @foreach($form->sections as $section)
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        @if($section->title)
                        <h3 class="font-semibold text-base mb-4">{{ $section->title }}</h3>
                        @endif

                        <div class="space-y-4">
                            @foreach($section->fields->sortBy('order') as $field)
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">
                                        {{ $field->label }}
                                        @if($field->is_required)<span class="text-error ml-1">*</span>@endif
                                    </span>
                                </label>

                                @switch($field->type)
                                    @case('text')
                                        <input type="text" name="answers[{{ $field->id }}]"
                                               value="{{ old("answers.{$field->id}") }}"
                                               class="input input-bordered @error("answers.{$field->id}") input-error @enderror"
                                               placeholder="{{ $field->placeholder }}"
                                               {{ $field->is_required ? 'required' : '' }} />
                                        @break
                                    @case('textarea')
                                        <textarea name="answers[{{ $field->id }}]" rows="3"
                                                  class="textarea textarea-bordered @error("answers.{$field->id}") textarea-error @enderror"
                                                  {{ $field->is_required ? 'required' : '' }}>{{ old("answers.{$field->id}") }}</textarea>
                                        @break
                                    @case('number')
                                        <input type="number" name="answers[{{ $field->id }}]"
                                               value="{{ old("answers.{$field->id}") }}"
                                               class="input input-bordered @error("answers.{$field->id}") input-error @enderror"
                                               {{ $field->is_required ? 'required' : '' }} />
                                        @break
                                    @case('date')
                                        <input type="date" name="answers[{{ $field->id }}]"
                                               value="{{ old("answers.{$field->id}") }}"
                                               class="input input-bordered @error("answers.{$field->id}") input-error @enderror"
                                               {{ $field->is_required ? 'required' : '' }} />
                                        @break
                                    @case('select')
                                        <select name="answers[{{ $field->id }}]"
                                                class="select select-bordered @error("answers.{$field->id}") select-error @enderror"
                                                {{ $field->is_required ? 'required' : '' }}>
                                            <option value="">Sélectionner...</option>
                                            @foreach($field->config['options'] ?? [] as $option)
                                            <option value="{{ $option }}" {{ old("answers.{$field->id}") == $option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @case('radio')
                                        <div class="flex flex-col gap-2 mt-1">
                                            @foreach($field->config['options'] ?? [] as $option)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="answers[{{ $field->id }}]"
                                                       value="{{ $option }}" class="radio radio-primary radio-sm"
                                                       {{ old("answers.{$field->id}") == $option ? 'checked' : '' }}
                                                       {{ $field->is_required ? 'required' : '' }} />
                                                <span class="text-sm">{{ $option }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                        @break
                                    @default
                                        <input type="text" name="answers[{{ $field->id }}]"
                                               class="input input-bordered"
                                               {{ $field->is_required ? 'required' : '' }} />
                                @endswitch

                                @error("answers.{$field->id}")
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Annuler</a>
                <button type="submit" class="btn btn-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
                    Soumettre le rapport urgent
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
