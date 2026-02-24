<x-app-layout>
    <x-slot name="title">Nouvel utilisateur</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('employees.index') }}" class="btn btn-ghost btn-sm">← Retour</a>
            <h1 class="text-2xl font-bold">Nouvel utilisateur</h1>
        </div>

        <div class="alert alert-info mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <div class="font-bold">Identifiants générés automatiquement</div>
                <div class="text-sm">Login : <code>nom@telephone.org</code> &nbsp;·&nbsp; Mot de passe : <code>MLtelephone</code></div>
            </div>
        </div>

        <div x-data="createUser()" class="card bg-base-100 shadow">
            <div class="card-body space-y-6">

                {{-- Étape 1 : Type d'utilisateur --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold text-base">Type d'utilisateur</span>
                    </label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($creatableRoles as $role)
                        <label class="label cursor-pointer gap-2 border rounded-lg px-4 py-2
                                      hover:bg-base-200 has-[:checked]:border-primary has-[:checked]:bg-primary/10">
                            <input type="radio" name="_role_display" value="{{ $role }}"
                                   class="radio radio-primary radio-sm"
                                   x-model="selectedRole" />
                            <span class="label-text capitalize font-medium">{{ ucfirst($role) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="role" :value="selectedRole" />

                    {{-- Étape 2 : Champs communs --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Prénom *</span></label>
                            <input type="text" name="firstname" value="{{ old('firstname') }}" required
                                   class="input input-bordered @error('firstname') input-error @enderror"
                                   placeholder="Prénom" />
                            @error('firstname')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Nom *</span></label>
                            <input type="text" name="lastname" value="{{ old('lastname') }}" required
                                   class="input input-bordered @error('lastname') input-error @enderror"
                                   placeholder="Nom" />
                            @error('lastname')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                        </div>
                        <div class="form-control sm:col-span-2">
                            <label class="label"><span class="label-text">Téléphone *</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                   class="input input-bordered @error('phone') input-error @enderror"
                                   placeholder="Ex: 0612345678" />
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">Sert à générer les identifiants de connexion</span>
                            </label>
                            @error('phone')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                        </div>
                    </div>

                    {{-- Étape 3A : Superviseur → dropdown Manager --}}
                    <div x-show="selectedRole === 'superviseur'" x-cloak class="form-control">
                        <label class="label"><span class="label-text font-medium">Son Manager *</span></label>
                        <select name="manager_id"
                                class="select select-bordered w-full @error('manager_id') select-error @enderror">
                            <option value="">— Choisir un Manager —</option>
                            @foreach($managers as $m)
                            <option value="{{ $m->id }}" {{ old('manager_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->firstname }} {{ $m->lastname }}
                            </option>
                            @endforeach
                        </select>
                        @error('manager_id')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>

                    {{-- Étape 3B : Employé → dropdown Superviseur --}}
                    <div x-show="selectedRole === 'employe'" x-cloak class="form-control">
                        <label class="label"><span class="label-text font-medium">Son Superviseur *</span></label>
                        <select name="supervisor_id"
                                class="select select-bordered w-full @error('supervisor_id') select-error @enderror">
                            <option value="">— Choisir un Superviseur —</option>
                            @foreach($superviseurs as $s)
                            <option value="{{ $s->id }}" {{ old('supervisor_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->firstname }} {{ $s->lastname }}
                            </option>
                            @endforeach
                        </select>
                        @error('supervisor_id')<label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>@enderror
                    </div>

                    {{-- Étape 3C : Privilèges (Manager + Superviseur uniquement) --}}
                    <div x-show="selectedRole === 'manager' || selectedRole === 'superviseur'" x-cloak>
                        <div class="divider text-sm">Privilèges</div>
                        <div class="flex flex-col gap-2">

                            @if(in_array('can_create_forms', $availablePrivileges))
                            <label class="label cursor-pointer justify-start gap-3"
                                   x-show="selectedRole === 'manager'">
                                <input type="checkbox" name="privileges[]" value="can_create_forms"
                                       class="checkbox checkbox-primary" />
                                <span class="label-text">Peut créer des questionnaires</span>
                            </label>
                            @endif

                            @if(in_array('can_create_superviseurs', $availablePrivileges))
                            <label class="label cursor-pointer justify-start gap-3"
                                   x-show="selectedRole === 'manager'">
                                <input type="checkbox" name="privileges[]" value="can_create_superviseurs"
                                       class="checkbox checkbox-primary" />
                                <span class="label-text">Peut créer des Superviseurs</span>
                            </label>
                            @endif

                            @if(in_array('can_create_employes', $availablePrivileges))
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="privileges[]" value="can_create_employes"
                                       class="checkbox checkbox-primary" />
                                <span class="label-text">Peut créer des Employés</span>
                            </label>
                            @endif

                            @if(in_array('can_delegate', $availablePrivileges))
                            <label class="label cursor-pointer justify-start gap-3"
                                   x-show="selectedRole === 'manager'">
                                <input type="checkbox" name="privileges[]" value="can_delegate"
                                       class="checkbox checkbox-primary" />
                                <span class="label-text">Peut déléguer ses privilèges</span>
                            </label>
                            @endif

                        </div>
                    </div>

                    <div class="flex gap-3 justify-end pt-2">
                        <a href="{{ route('employees.index') }}" class="btn btn-ghost">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer le compte</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
    function createUser() {
        return {
            selectedRole: '{{ $creatableRoles[0] ?? "" }}',
        }
    }
    </script>
</x-app-layout>
