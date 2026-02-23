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

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Département</span></label>
                            <input type="text" name="department" value="{{ old('department') }}" class="input input-bordered" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Poste</span></label>
                            <input type="text" name="job_title" value="{{ old('job_title') }}" class="input input-bordered" />
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Manager direct</span></label>
                        <select name="manager_id" class="select select-bordered w-full">
                            <option value="">Aucun</option>
                            @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                {{ $manager->full_name }}
                            </option>
                            @endforeach
                        </select>
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
