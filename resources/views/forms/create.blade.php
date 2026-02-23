<x-app-layout>
    <x-slot name="title">Nouveau formulaire</x-slot>

    <div
        x-data="{
            title: '',
            sections: [],
            addSection() {
                this.sections.push({ id: Date.now(), title: 'Nouvelle section', fields: [] });
            },
            removeSection(index) {
                this.sections.splice(index, 1);
            },
            addField(sectionIndex, type) {
                this.sections[sectionIndex].fields.push({
                    id: Date.now(),
                    type: type,
                    label: 'Nouveau champ',
                    placeholder: '',
                    is_required: false,
                    config: { choices: [] },
                    showOptions: false,
                });
            },
            removeField(sectionIndex, fieldIndex) {
                this.sections[sectionIndex].fields.splice(fieldIndex, 1);
            },
            fieldTypes: [
                { type: 'text', label: 'Texte court', icon: '📝' },
                { type: 'textarea', label: 'Texte long', icon: '📄' },
                { type: 'select', label: 'Liste déroulante', icon: '▼' },
                { type: 'radio', label: 'Choix unique', icon: '⊙' },
                { type: 'checkbox', label: 'Cases à cocher', icon: '☑' },
                { type: 'date', label: 'Date', icon: '📅' },
                { type: 'number', label: 'Nombre', icon: '🔢' },
                { type: 'file', label: 'Fichier/Photo', icon: '📎' },
                { type: 'rating', label: 'Étoiles', icon: '⭐' },
            ]
        }"
        x-init="addSection()"
    >
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('forms.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
            <h1 class="text-2xl font-bold flex-1">Nouveau formulaire</h1>
        </div>

        <form method="POST" action="{{ route('forms.store') }}" id="form-builder">
            @csrf

            {{-- Titre & description --}}
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-semibold text-lg">Titre du formulaire *</span></label>
                        <input type="text" name="title" x-model="title" class="input input-bordered input-lg w-full" placeholder="Ex: Rapport de visite terrain" required />
                    </div>
                    <div class="form-control w-full mt-4">
                        <label class="label"><span class="label-text font-medium">Description / Instructions</span></label>
                        <textarea name="description" class="textarea textarea-bordered" rows="3" placeholder="Instructions pour les employés..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Sections --}}
            <template x-for="(section, sIndex) in sections" :key="section.id">
                <div class="card bg-base-100 shadow mb-4">
                    <div class="card-body">
                        <div class="flex items-center gap-3 mb-4">
                            <input
                                :name="`sections[${sIndex}][title]`"
                                x-model="section.title"
                                class="input input-bordered flex-1 font-semibold"
                                placeholder="Titre de la section"
                            />
                            <button type="button" class="btn btn-ghost btn-sm text-error" x-on:click="removeSection(sIndex)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>

                        {{-- Champs de la section --}}
                        <template x-for="(field, fIndex) in section.fields" :key="field.id">
                            <div class="border border-base-300 rounded-xl p-4 mb-3 bg-base-50">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="badge badge-outline badge-sm" x-text="field.type"></span>
                                    <input
                                        :name="`sections[${sIndex}][fields][${fIndex}][type]`"
                                        :value="field.type"
                                        type="hidden"
                                    />
                                    <div class="flex-1">
                                        <input
                                            :name="`sections[${sIndex}][fields][${fIndex}][label]`"
                                            x-model="field.label"
                                            class="input input-bordered input-sm w-full"
                                            placeholder="Libellé du champ"
                                        />
                                    </div>
                                    <label class="label cursor-pointer gap-1">
                                        <input
                                            type="checkbox"
                                            :name="`sections[${sIndex}][fields][${fIndex}][is_required]`"
                                            x-model="field.is_required"
                                            class="checkbox checkbox-sm checkbox-error"
                                        />
                                        <span class="label-text text-xs">Requis</span>
                                    </label>
                                    <button type="button" class="btn btn-ghost btn-xs text-error" x-on:click="removeField(sIndex, fIndex)">✕</button>
                                </div>

                                <input
                                    :name="`sections[${sIndex}][fields][${fIndex}][placeholder]`"
                                    x-model="field.placeholder"
                                    class="input input-bordered input-sm w-full"
                                    placeholder="Texte d'aide (optionnel)"
                                />

                                {{-- Options pour select/radio/checkbox --}}
                                <template x-if="['select','radio','checkbox'].includes(field.type)">
                                    <div class="mt-3">
                                        <div class="text-xs font-medium mb-2">Options :</div>
                                        <template x-for="(choice, cIndex) in (field.config.choices || [])" :key="cIndex">
                                            <div class="flex items-center gap-2 mb-1">
                                                <input
                                                    :name="`sections[${sIndex}][fields][${fIndex}][config][choices][]`"
                                                    x-model="field.config.choices[cIndex]"
                                                    class="input input-bordered input-xs flex-1"
                                                    placeholder="Option..."
                                                />
                                                <button type="button" class="btn btn-ghost btn-xs" x-on:click="field.config.choices.splice(cIndex, 1)">✕</button>
                                            </div>
                                        </template>
                                        <button type="button" class="btn btn-ghost btn-xs mt-1" x-on:click="field.config.choices = field.config.choices || []; field.config.choices.push('')">
                                            + Ajouter une option
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Ajouter un champ --}}
                        <div class="divider text-xs">Ajouter un champ</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="ft in fieldTypes" :key="ft.type">
                                <button
                                    type="button"
                                    class="btn btn-outline btn-xs gap-1"
                                    x-on:click="addField(sIndex, ft.type)"
                                >
                                    <span x-text="ft.icon"></span>
                                    <span x-text="ft.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Ajouter une section --}}
            <button type="button" class="btn btn-outline btn-block mb-6" x-on:click="addSection()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Ajouter une section
            </button>

            {{-- Actions --}}
            <div class="flex gap-3 justify-end">
                <a href="{{ route('forms.index') }}" class="btn btn-ghost">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Créer le formulaire
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
