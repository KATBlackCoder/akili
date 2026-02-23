@props([
    'id',
    'message' => 'Êtes-vous sûr de vouloir effectuer cette action ?',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'confirmClass' => 'btn-error',
])

<dialog id="{{ $id }}" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Confirmation</h3>
        <p class="py-4">{{ $message }}</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">{{ $cancelText }}</button>
            </form>
            {{ $slot }}
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>Fermer</button>
    </form>
</dialog>
