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
                    <div class="flex justify-between items-start">
                        <h3 class="card-title text-base line-clamp-2">{{ $form->title }}</h3>
                        <x-badge-status :status="$form->is_active ? 'active' : 'inactive'" />
                    </div>
                    @if($form->description)
                    <p class="text-sm text-base-content/60 line-clamp-2">{{ $form->description }}</p>
                    @endif
                    <div class="text-xs text-base-content/50 mt-2">
                        Créé par {{ $form->creator->full_name }} · {{ $form->created_at->format('d/m/Y') }}
                    </div>
                    <div class="card-actions justify-between items-center mt-2">
                        <div class="flex gap-2">
                            <a href="{{ route('forms.show', $form) }}" class="btn btn-ghost btn-xs">Voir</a>
                            <a href="{{ route('forms.edit', $form) }}" class="btn btn-ghost btn-xs">Modifier</a>
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
</x-app-layout>
