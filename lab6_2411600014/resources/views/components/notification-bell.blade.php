<div class="dropdown">
    <button class="btn btn-outline-light btn-sm position-relative px-3" type="button" id="notificationBell"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Low unit load alerts">
        {{-- bell icon (inline SVG, no extra library needed) --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
        </svg>
        <span id="notificationCount"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $unreadCount > 0 ? '' : 'd-none' }}">{{ $unreadCount }}</span>
    </button>

    <div class="dropdown-menu dropdown-menu-end shadow notification-menu p-0" aria-labelledby="notificationBell">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <span class="fw-bold text-dark">Notifications</span>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Mark all as read</button>
                </form>
            @endif
        </div>

        @forelse($notifications as $notification)
            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                @csrf
                <button type="submit" class="dropdown-item py-2 border-bottom {{ $notification->read_at ? 'text-muted' : 'fw-semibold bg-light' }}">
                    <div class="small">{{ $notification->data['message'] ?? 'Notification' }}</div>
                    <div class="text-muted fw-normal" style="font-size: .75rem;">
                        {{ $notification->created_at->diffForHumans() }}
                        @unless($notification->read_at) &middot; <span class="text-danger">new</span> @endunless
                    </div>
                </button>
            </form>
        @empty
            <div class="px-3 py-4 text-center text-muted small">No notifications yet.</div>
        @endforelse
    </div>
</div>
