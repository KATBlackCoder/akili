<x-app-layout>
    <x-slot name="title">Employés</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Employés</h1>
                <p class="text-base-content/60">{{ $employees->total() }} employé(s)</p>
            </div>
            @if(auth()->user()->canCreateSuperviseurs() || auth()->user()->canCreateEmployes() || auth()->user()->hasRole('super-admin'))
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                Nouvel employé
            </a>
            @endif
        </div>

        {{-- Recherche --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body py-3">
                <input
                    type="text"
                    placeholder="Rechercher par nom, username..."
                    class="input input-bordered w-full"
                    hx-get="{{ route('employees.index') }}"
                    hx-trigger="keyup changed delay:500ms"
                    hx-target="#employees-table"
                    hx-include="this"
                    name="search"
                />
            </div>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="overflow-x-auto" id="employees-table">
                @include('employees.partials.table', ['employees' => $employees])
            </div>
        </div>

        {{ $employees->links() }}
    </div>
</x-app-layout>
