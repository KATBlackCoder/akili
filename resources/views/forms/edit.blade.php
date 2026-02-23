<x-app-layout>
    <x-slot name="title">Modifier {{ $form->title }}</x-slot>

    <div class="max-w-xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('forms.show', $form) }}" class="btn btn-ghost btn-sm">← Retour</a>
            <h1 class="text-2xl font-bold">Modifier le formulaire</h1>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('forms.update', $form) }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Titre *</span></label>
                        <input type="text" name="title" value="{{ old('title', $form->title) }}" class="input input-bordered" required />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Description</span></label>
                        <textarea name="description" class="textarea textarea-bordered" rows="3">{{ old('description', $form->description) }}</textarea>
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary"
                                {{ $form->is_active ? 'checked' : '' }} />
                            <span class="label-text font-medium">Formulaire actif</span>
                        </label>
                    </div>

                    <div class="flex gap-3 justify-end pt-4">
                        <a href="{{ route('forms.show', $form) }}" class="btn btn-ghost">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
