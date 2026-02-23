<table class="table w-full">
    <thead>
        <tr class="text-xs uppercase text-base-content/40">
            <th>Employé</th>
            <th class="hidden sm:table-cell">Département</th>
            <th class="hidden sm:table-cell">Manager</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($employees as $employee)
        <tr class="hover">
            <td>
                <div class="flex items-center gap-3">
                    <div class="bg-primary text-primary-content rounded-xl w-9 h-9 flex items-center justify-center font-medium text-sm flex-shrink-0">
                        <span>{{ substr($employee->firstname, 0, 1) }}{{ substr($employee->lastname, 0, 1) }}</span>
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium text-sm truncate">{{ $employee->full_name }}</div>
                        <div class="text-xs text-base-content/40 truncate">{{ $employee->username }}</div>
                        {{-- Département visible uniquement mobile --}}
                        @if($employee->department)
                        <div class="text-xs text-base-content/50 sm:hidden mt-0.5">{{ $employee->department }}</div>
                        @endif
                    </div>
                </div>
            </td>
            <td class="hidden sm:table-cell text-sm text-base-content/70">{{ $employee->department ?? '—' }}</td>
            <td class="hidden sm:table-cell text-sm text-base-content/70">{{ $employee->manager?->full_name ?? '—' }}</td>
            <td>
                <x-badge-status :status="$employee->is_active ? 'active' : 'inactive'" />
            </td>
            <td>
                <div class="flex gap-1 items-center flex-wrap">
                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-ghost btn-xs">Voir</a>
                    @if(auth()->user()->hasRole('super-admin'))
                    <form method="POST" action="{{ route('employees.toggle', $employee) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-ghost btn-xs hidden sm:inline-flex {{ $employee->is_active ? 'text-warning' : 'text-success' }}">
                            {{ $employee->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    @if(auth()->id() !== $employee->id)
                    <form method="POST" action="{{ route('employees.destroy', $employee) }}"
                          onsubmit="return confirm('Supprimer {{ addslashes($employee->full_name) }} ? Cette action est irréversible.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">
                            <span class="hidden sm:inline">Supprimer</span>
                            <span class="sm:hidden">✕</span>
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-base-content/40 py-12 text-sm">Aucun employé trouvé</td>
        </tr>
        @endforelse
    </tbody>
</table>
