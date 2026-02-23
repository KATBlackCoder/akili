<x-guest-layout>
    <div class="text-center mb-6">
        <div class="badge badge-warning badge-lg mb-3">Première connexion</div>
        <h2 class="text-2xl font-bold">Changer votre mot de passe</h2>
        <p class="text-base-content/60 text-sm mt-2">
            Pour sécuriser votre compte, vous devez définir un nouveau mot de passe avant de continuer.
        </p>
    </div>

    <form method="POST" action="{{ route('first-login') }}">
        @csrf

        <div class="form-control w-full">
            <label class="label" for="password">
                <span class="label-text font-medium">Nouveau mot de passe</span>
            </label>
            <input
                id="password"
                type="password"
                name="password"
                class="input input-bordered w-full @error('password') input-error @enderror"
                required
                autofocus
                autocomplete="new-password"
            />
            @error('password')
                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
            @enderror
        </div>

        <div class="form-control w-full mt-4">
            <label class="label" for="password_confirmation">
                <span class="label-text font-medium">Confirmer le mot de passe</span>
            </label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="input input-bordered w-full"
                required
                autocomplete="new-password"
            />
        </div>

        <button type="submit" class="btn btn-primary w-full mt-6">
            Enregistrer et continuer
        </button>
    </form>
</x-guest-layout>
