<x-app-layout>
    <x-slot name="title">{{ $form->title }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('forms.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold">{{ $form->title }}</h1>
                @if($form->description)
                <p class="text-base-content/60">{{ $form->description }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('forms.edit', $form) }}" class="btn btn-outline btn-sm">Modifier</a>
                <a href="{{ route('forms.export.pdf', $form) }}" class="btn btn-outline btn-sm">PDF</a>
                <a href="{{ route('forms.export.excel', $form) }}" class="btn btn-outline btn-sm">Excel</a>
            </div>
        </div>

        @foreach($form->sections as $section)
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="font-semibold text-lg mb-4">{{ $section->title }}</h2>
                <div class="space-y-3">
                    @foreach($section->fields as $field)
                    <div class="flex items-center justify-between py-2 border-b border-base-200 last:border-0">
                        <div>
                            <span class="font-medium text-sm">{{ $field->label }}</span>
                            @if($field->is_required)
                            <span class="text-error text-xs ml-1">*</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-outline badge-xs">{{ $field->type }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        {{-- Stats d'assignation --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="font-semibold mb-3">Assignations</h3>
                <div class="stats stats-horizontal shadow w-full">
                    <div class="stat">
                        <div class="stat-title">Total</div>
                        <div class="stat-value text-xl">{{ $form->assignments->count() }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">En attente</div>
                        <div class="stat-value text-xl text-warning">{{ $form->assignments->where('status', 'pending')->count() }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Complétés</div>
                        <div class="stat-value text-xl text-success">{{ $form->assignments->where('status', 'completed')->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
