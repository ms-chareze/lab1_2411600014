@php
    $bellUser    = auth()->user();
    $unreadCount = $bellUser->unreadNotifications()->count();
    $bellItems   = $bellUser->notifications()->latest()->take(5)->get();
@endphp

<li class="nav-item dropdown me-lg-1">
    <a class="nav-link position-relative" href="#" id="notificationBell" role="button"
       data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
       aria-label="Notifications ({{ $unreadCount }} unread)">
        <span aria-hidden="true">🔔</span>
        @if($unreadCount > 0)
            <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" id="notificationCount">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-end notification-menu p-0" aria-labelledby="notificationBell">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <strong>Notifications</strong>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0">Mark all as read</button>
                </form>
            @endif
        </div>

        @forelse($bellItems as $n)
            <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                @csrf
                <button type="submit" class="notification-item {{ $n->read_at ? '' : 'unread' }}">
                    <span class="d-block fw-semibold small">
                        {{ $n->data['product_name'] ?? 'Notification' }}
                        @if(isset($n->data['sku']))
                            <span class="text-muted fw-normal">({{ $n->data['sku'] }})</span>
                        @endif
                    </span>
                    <span class="d-block small">{{ $n->data['message'] ?? '' }}</span>
                    <span class="d-block text-muted" style="font-size: .75rem;">{{ $n->created_at->diffForHumans() }}</span>
                </button>
            </form>
        @empty
            <div class="px-3 py-4 text-center text-muted small">No notifications yet.</div>
        @endforelse
    </div>
</li>
