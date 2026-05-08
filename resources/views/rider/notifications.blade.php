@extends('rider.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bx bx-bell me-2"></i>Notifications
            </h4>
            <p class="text-muted mb-0">Stay updated with your deliveries</p>
        </div>
        @if(Auth::user()->unreadNotifications->count() > 0)
        <form action="{{ route('rider.notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-label-primary">
                <i class="bx bx-check-double me-1"></i>Mark All Read
            </button>
        </form>
        @endif
    </div>

    @if($notifications->count() > 0)
    <div class="card">
        <div class="list-group list-group-flush">
            @foreach($notifications as $notification)
            <div class="list-group-item list-group-item-action {{ is_null($notification->read_at) ? 'bg-label-primary' : '' }}">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded-circle {{ is_null($notification->read_at) ? 'bg-primary' : 'bg-secondary' }}">
                                <i class="bx {{ $notification->data['icon'] ?? 'bx-package' }}"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">{{ $notification->data['message'] ?? 'You have a new notification' }}</p>
                        
                        @if(isset($notification->data['action_url']))
                        <a href="{{ $notification->data['action_url'] }}" class="btn btn-sm btn-primary">
                            {{ $notification->data['action_text'] ?? 'View' }}
                        </a>
                        @endif
                        
                        @if(is_null($notification->read_at))
                        <form action="{{ route('rider.notifications.read', $notification->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-label-secondary ms-2">
                                <i class="bx bx-check me-1"></i>Mark Read
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} notifications
                </div>
                <div>
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>

    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-bell-off bx-lg text-muted mb-3"></i>
            <h5 class="mb-2">No Notifications</h5>
            <p class="text-muted mb-0">You're all caught up! Check back later for updates.</p>
        </div>
    </div>
    @endif

</div>
@endsection