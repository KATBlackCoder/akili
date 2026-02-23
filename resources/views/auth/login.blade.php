<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-2xl font-bold text-center mb-6">Connexion</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-control w-full">
            <label class="label" for="username">
                <span class="label-text font-medium">Identifiant</span>
            </label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username') }}"
                class="input input-bordered w-full @error('username') input-error @enderror"
                placeholder="nom@telephone.org"
                required
                autofocus
                autocomplete="username"
            />
            @error('username')
                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
            @enderror
        </div>

        <div class="form-control w-full mt-4">
            <label class="label" for="password">
                <span class="label-text font-medium">Mot de passe</span>
            </label>
            <input
                id="password"
                type="password"
                name="password"
                class="input input-bordered w-full @error('password') input-error @enderror"
                required
                autocomplete="current-password"
            />
            @error('password')
                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
            @enderror
        </div>

        <div class="flex items-center justify-between mt-4">
            <label class="label cursor-pointer gap-2">
                <input type="checkbox" name="remember" class="checkbox checkbox-sm" />
                <span class="label-text">Se souvenir de moi</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-full mt-6">
            Se connecter
        </button>
    </form>
</x-guest-layout>
