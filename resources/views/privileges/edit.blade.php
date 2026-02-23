<x-app-layout>
    <x-slot name="title">Privilèges de {{ $user->full_name }}</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('employees.show', $user) }}" class="btn btn-ghost btn-sm">← Retour</a>
            <h1 class="text-2xl font-bold">Privilèges Manager</h1>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-primary text-primary-content rounded-xl w-12 h-12 flex items-center justify-center font-semibold flex-shrink-0">
                        <span>{{ substr($user->firstname, 0, 1) }}{{ substr($user->lastname, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="font-bold">{{ $user->full_name }}</div>
                        <div class="text-sm text-base-content/60">{{ $user->username }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('managers.privileges.update', $user) }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="can_create_forms" value="1" class="toggle toggle-primary"
                                {{ $privilege->can_create_forms ? 'checked' : '' }} />
                            <div>
                                <div class="font-medium">Créer des formulaires</div>
                                <div class="text-sm text-base-content/60">Peut créer et gérer des questionnaires</div>
                            </div>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="can_create_users" value="1" class="toggle toggle-primary"
                                {{ $privilege->can_create_users ? 'checked' : '' }} />
                            <div>
                                <div class="font-medium">Créer des utilisateurs</div>
                                <div class="text-sm text-base-content/60">Peut créer de nouveaux comptes employé</div>
                            </div>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="can_delegate" value="1" class="toggle toggle-primary"
                                {{ $privilege->can_delegate ? 'checked' : '' }} />
                            <div>
                                <div class="font-medium">Déléguer des privilèges</div>
                                <div class="text-sm text-base-content/60">Peut transmettre ses propres privilèges à d'autres managers</div>
                            </div>
                        </label>
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
