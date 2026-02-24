<x-app-layout>
    <x-slot name="title">Nouvel employé</x-slot>

    <div class="max-w-xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('employees.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
            <h1 class="text-2xl font-bold">Nouvel employé</h1>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <div class="font-bold">Génération automatique des identifiants</div>
                        <div class="text-sm">Identifiant : <code>nom@telephone.org</code> · Mot de passe : <code>MLtelephone</code></div>
                    </div>
                </div>

                <form method="POST" action="{{ route('employees.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Prénom *</span></label>
                            <input type="text" name="firstname" value="{{ old('firstname') }}" class="input input-bordered @error('firstname') input-error @enderror" required />
                            @error('firstname')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Nom *</span></label>
                            <input type="text" name="lastname" value="{{ old('lastname') }}" class="input input-bordered @error('lastname') input-error @enderror" required />
                            @error('lastname')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Téléphone *</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="input input-bordered @error('phone') input-error @enderror" placeholder="0612345678" required />
                        @error('phone')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>

                    @if(auth()->user()->hasRole('manager'))
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Rôle *</span></label>
                        <select name="role" class="select select-bordered w-full" required
                                hx-get="{{ route('employees.create') }}"
                                hx-trigger="change"
                                hx-target="#role-dependant-fields"
                                hx-include="this"
                                hx-swap="innerHTML">
                            <option value="superviseur" {{ old('role') == 'superviseur' ? 'selected' : '' }}>Superviseur</option>
                            <option value="employe" {{ old('role', 'employe') == 'employe' ? 'selected' : '' }}>Employé</option>
                        </select>
                    </div>
                    @endif

                    <div id="role-dependant-fields">
                        @if(count($superviseurs ?? []) > 0)
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Superviseur *</span></label>
                            <select name="supervisor_id" class="select select-bordered w-full">
                                <option value="">Sélectionner un superviseur</option>
                                @foreach($superviseurs ?? [] as $superviseur)
                                <option value="{{ $superviseur->id }}" {{ old('supervisor_id') == $superviseur->id ? 'selected' : '' }}>
                                    {{ $superviseur->full_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if(count($groups ?? []) > 0)
                        <div class="form-control mt-4">
                            <label class="label"><span class="label-text font-medium">Groupe *</span></label>
                            <select name="group_id" class="select select-bordered w-full">
                                <option value="">Sélectionner un groupe</option>
                                @foreach($groups ?? [] as $group)
                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Date d'embauche</span></label>
                        <input type="date" name="hired_at" value="{{ old('hired_at') }}" class="input input-bordered" />
                    </div>

                    <div class="flex gap-3 justify-end pt-4">
                        <a href="{{ route('employees.index') }}" class="btn btn-ghost">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer l'employé</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
