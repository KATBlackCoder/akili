<x-app-layout>
    <x-slot name="title">Congés</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Congés</h1>
                <p class="text-base-content/60">{{ $leaveRequests->total() }} demande(s)</p>
            </div>
            @if(auth()->user()->hasRole('employee'))
            <button class="btn btn-primary" x-on:click="document.getElementById('leave-modal').showModal()">
                Demander un congé
            </button>
            @endif
        </div>

        <div class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            @if(!auth()->user()->hasRole('employee'))<th>Employé</th>@endif
                            <th>Type</th>
                            <th>Période</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $leave)
                        <tr>
                            @if(!auth()->user()->hasRole('employee'))
                            <td>{{ $leave->employee->full_name }}</td>
                            @endif
                            <td>
                                <span class="badge badge-outline badge-sm">
                                    {{ ['paid' => 'Payé', 'unpaid' => 'Non payé', 'sick' => 'Maladie', 'other' => 'Autre'][$leave->type] }}
                                </span>
                            </td>
                            <td>{{ $leave->start_date->format('d/m/Y') }} → {{ $leave->end_date->format('d/m/Y') }}</td>
                            <td>{{ $leave->duration_in_days }} j</td>
                            <td><x-badge-status :status="$leave->status" /></td>
                            <td>
                                @if($leave->status === 'pending' && !auth()->user()->hasRole('employee'))
                                <div class="flex gap-1">
                                    <form method="POST" action="{{ route('leave-requests.approve', $leave) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-success btn-xs">Approuver</button>
                                    </form>
                                    <form method="POST" action="{{ route('leave-requests.reject', $leave) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-error btn-xs">Refuser</button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-base-content/50 py-12">Aucune demande de congé</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $leaveRequests->links() }}
    </div>

    {{-- Modal demande de congé --}}
    @if(auth()->user()->hasRole('employee'))
    <dialog id="leave-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Demander un congé</h3>
            <form method="POST" action="{{ route('leave-requests.store') }}" class="space-y-4">
                @csrf
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Type de congé *</span></label>
                    <select name="type" class="select select-bordered w-full" required>
                        <option value="paid">Congé payé</option>
                        <option value="unpaid">Congé non payé</option>
                        <option value="sick">Maladie</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Date de début *</span></label>
                        <input type="date" name="start_date" class="input input-bordered" min="{{ today()->format('Y-m-d') }}" required />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Date de fin *</span></label>
                        <input type="date" name="end_date" class="input input-bordered" min="{{ today()->format('Y-m-d') }}" required />
                    </div>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Motif</span></label>
                    <textarea name="reason" class="textarea textarea-bordered" rows="3" placeholder="Motif de la demande (optionnel)..."></textarea>
                </div>
                <div class="modal-action">
                    <form method="dialog"><button class="btn btn-ghost">Annuler</button></form>
                    <button type="submit" class="btn btn-primary">Soumettre</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button>Fermer</button></form>
    </dialog>
    @endif
</x-app-layout>
