@props(['showLabel' => false])

<div
    hx-get="{{ route('notifications.count') }}"
    hx-trigger="load, every 60s"
    hx-target="this"
    hx-swap="innerHTML"
>
    @include('notifications.partials.count', ['count' => auth()->user()->unreadNotifications()->count()])
</div>
