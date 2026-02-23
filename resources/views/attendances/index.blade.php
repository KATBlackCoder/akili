<x-app-layout>
    <x-slot name="title">Présences</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Présences</h1>
            <div class="flex gap-2">
                <input type="month" value="{{ $month }}" class="input input-bordered input-sm"
                    hx-get="{{ route('attendances.index') }}"
                    hx-trigger="change"
                    hx-target="#attendance-grid"
                    hx-include="[name='employee_id']"
                    name="month" />
                @if(!auth()->user()->hasRole('employee'))
                <select name="employee_id" class="select select-bordered select-sm"
                    hx-get="{{ route('attendances.index') }}"
                    hx-trigger="change"
                    hx-target="#attendance-grid"
                    hx-include="[name='month']">
                    <option value="">Tous les employés</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>

        {{-- Grille des présences --}}
        <div id="attendance-grid">
            @include('attendances.partials.grid', compact('attendances', 'month', 'employeeId', 'employees'))
        </div>
    </div>

    {{-- Modal saisie présence --}}
    <dialog id="attendance-modal" class="modal" x-data="{ date: '', userId: {{ auth()->id() }} }">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Saisir une présence</h3>
            <form method="POST" action="{{ route('attendances.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="date" x-bind:value="date" />
                @if(!auth()->user()->hasRole('employee'))
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Employé *</span></label>
                    <select name="user_id" class="select select-bordered w-full" required>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <input type="hidden" name="user_id" value="{{ auth()->id() }}" />
                @endif
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Heure d'arrivée</span></label>
                        <input type="time" name="check_in" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Heure de départ</span></label>
                        <input type="time" name="check_out" class="input input-bordered" />
                    </div>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Note</span></label>
                    <input type="text" name="note" class="input input-bordered" placeholder="Retard, absence partielle..." />
                </div>
                <div class="modal-action">
                    <form method="dialog"><button class="btn btn-ghost">Annuler</button></form>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button>Fermer</button></form>
    </dialog>
</x-app-layout>
