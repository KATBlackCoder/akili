@props(['status'])

@php
$classes = match($status) {
    'pending'   => 'badge-warning',
    'completed' => 'badge-success',
    'returned'  => 'badge-error',
    'corrected' => 'badge-info',
    'expired'   => 'badge-neutral',
    'approved'  => 'badge-success',
    'rejected'  => 'badge-error',
    'submitted' => 'badge-primary',
    'active'    => 'badge-success',
    'inactive'  => 'badge-neutral',
    default     => 'badge-ghost',
};

$labels = [
    'pending'   => 'En attente',
    'completed' => 'Complété',
    'returned'  => 'Renvoyé',
    'corrected' => 'Corrigé',
    'expired'   => 'Expiré',
    'approved'  => 'Approuvé',
    'rejected'  => 'Refusé',
    'submitted' => 'Soumis',
    'active'    => 'Actif',
    'inactive'  => 'Inactif',
];
@endphp

<span class="badge {{ $classes }} badge-sm">{{ $labels[$status] ?? $status }}</span>
