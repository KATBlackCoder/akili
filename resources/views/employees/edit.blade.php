<x-app-layout>
    <x-slot name="title">Modifier {{ $user->full_name }}</x-slot>

    <div class="max-w-xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('employees.show', $user) }}" class="btn btn-ghost btn-sm">← Retour</a>
            <h1 class="text-2xl font-bold">Modifier le profil</h1>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('employees.update', $user) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Prénom *</span></label>
                            <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}" class="input input-bordered" required />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Nom *</span></label>
                            <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}" class="input input-bordered" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Département</span></label>
                            <input type="text" name="department" value="{{ old('department', $user->department) }}" class="input input-bordered" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Poste</span></label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" class="input input-bordered" />
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Manager direct</span></label>
                        <select name="manager_id" class="select select-bordered w-full">
                            <option value="">Aucun</option>
                            @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ $user->manager_id == $manager->id ? 'selected' : '' }}>
                                {{ $manager->full_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Date d'embauche</span></label>
                        <input type="date" name="hired_at" value="{{ old('hired_at', $user->hired_at?->format('Y-m-d')) }}" class="input input-bordered" />
                    </div>

                    <div class="flex gap-3 justify-end pt-4">
                        <a href="{{ route('employees.show', $user) }}" class="btn btn-ghost">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
