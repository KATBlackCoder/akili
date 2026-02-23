<x-app-layout>
    <x-slot name="title">Déléguer des privilèges</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('employees.show', $user) }}" class="btn btn-ghost btn-sm">← Retour</a>
            <h1 class="text-2xl font-bold">Déléguer des privilèges</h1>
        </div>

        <div class="alert alert-info mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Vous ne pouvez déléguer que les privilèges que vous possédez vous-même.</span>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('managers.delegate', $user) }}" class="space-y-4">
                    @csrf

                    @if($grantorPrivilege?->can_create_forms)
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="can_create_forms" value="1" class="toggle toggle-primary"
                                {{ $targetPrivilege->can_create_forms ? 'checked' : '' }} />
                            <div>
                                <div class="font-medium">Créer des formulaires</div>
                            </div>
                        </label>
                    </div>
                    @endif

                    @if($grantorPrivilege?->can_create_users)
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="can_create_users" value="1" class="toggle toggle-primary"
                                {{ $targetPrivilege->can_create_users ? 'checked' : '' }} />
                            <div>
                                <div class="font-medium">Créer des utilisateurs</div>
                            </div>
                        </label>
                    </div>
                    @endif

                    @if($grantorPrivilege?->can_delegate)
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="can_delegate" value="1" class="toggle toggle-primary"
                                {{ $targetPrivilege->can_delegate ? 'checked' : '' }} />
                            <div>
                                <div class="font-medium">Déléguer des privilèges</div>
                            </div>
                        </label>
                    </div>
                    @endif

                    <div class="flex gap-3 justify-end pt-4">
                        <a href="{{ route('employees.show', $user) }}" class="btn btn-ghost">Annuler</a>
                        <button type="submit" class="btn btn-primary">Déléguer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
