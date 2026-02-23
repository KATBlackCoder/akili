<x-app-layout>
    <x-slot name="title">Notifications</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Notifications</h1>
            @if(auth()->user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf @method('PATCH')
                <button class="btn btn-ghost btn-sm">Tout marquer comme lu</button>
            </form>
            @endif
        </div>

        <div class="space-y-2">
            @forelse($notifications as $notification)
            <div class="card bg-base-100 shadow {{ $notification->read_at ? 'opacity-60' : 'border-l-4 border-primary' }}">
                <div class="card-body py-3 px-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="font-medium text-sm">{{ $notification->data['title'] ?? 'Notification' }}</div>
                            <div class="text-sm text-base-content/70 mt-1">{{ $notification->data['message'] ?? '' }}</div>
                            <div class="text-xs text-base-content/40 mt-2">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(!$notification->read_at)
                            <div class="w-2 h-2 bg-primary rounded-full flex-shrink-0"></div>
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-ghost btn-xs">Lu</button>
                            </form>
                            @endif
                            @if(isset($notification->data['url']))
                            <a href="{{ $notification->data['url'] }}" class="btn btn-primary btn-xs">Voir</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="card bg-base-100 shadow">
                <div class="card-body items-center text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    <p class="text-base-content/50 mt-4">Aucune notification</p>
                </div>
            </div>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
</x-app-layout>
