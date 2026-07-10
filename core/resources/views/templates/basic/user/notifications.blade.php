@extends($activeTemplate . 'layouts.master')
@section('content')

<div class="notif-page">

    <div class="notif-header">
        <h5 class="notif-title"><i class="las la-bell"></i> Notifications</h5>
        <span class="notif-count">{{ $notifications->total() }} total</span>
    </div>

    <div class="notif-list-card">
        @forelse($notifications as $notif)
        @php
            $typeIcon = match($notif->notification_type) {
                'email' => 'la-envelope',
                'sms'   => 'la-sms',
                'push'  => 'la-bell',
                default => 'la-bell',
            };
            $typeLabel = match($notif->notification_type) {
                'email' => 'Email',
                'sms'   => 'SMS',
                'push'  => 'Push',
                default => ucfirst($notif->notification_type),
            };
        @endphp
        <div class="notif-item">
            <div class="notif-icon-wrap notif-icon-{{ $notif->notification_type }}">
                <i class="las {{ $typeIcon }}"></i>
            </div>
            <div class="notif-body">
                <div class="notif-subject">{{ $notif->subject }}</div>
                <div class="notif-message">{{ $notif->message }}</div>
                <div class="notif-meta">
                    <span class="notif-type-badge">{{ $typeLabel }}</span>
                    <span class="notif-time">{{ $notif->created_at->format('M d, Y · h:i A') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="notif-empty">
            <i class="las la-bell-slash notif-empty-icon"></i>
            <h6>No Notifications Yet</h6>
            <p>You'll see system alerts and updates here.</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="notif-pagination">{{ paginateLinks($notifications) }}</div>
    @endif

</div>

@endsection

@push('style')
<style>
.notif-page {
    max-width: 760px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
}

.notif-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}
.notif-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.notif-title i { color: #6366F1; }
.notif-count {
    font-size: .78rem;
    color: #9CA3AF;
    font-weight: 500;
}

.notif-list-card {
    background: #fff;
    border: 1px solid #E5E9EF;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
}

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #F3F4F6;
    transition: background .15s;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #F9FAFB; }

.notif-icon-wrap {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    margin-top: .1rem;
}
.notif-icon-email  { background: #EEF2FF; color: #6366F1; }
.notif-icon-sms    { background: #F0FDF4; color: #16A34A; }
.notif-icon-push   { background: #FFF7ED; color: #EA580C; }

.notif-body { flex: 1; min-width: 0; }
.notif-subject {
    font-size: .88rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: .25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.notif-message {
    font-size: .82rem;
    color: #4B5563;
    line-height: 1.5;
    margin-bottom: .45rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.notif-meta {
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-wrap: wrap;
}
.notif-type-badge {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #EEF2FF;
    color: #6366F1;
}
.notif-time {
    font-size: .74rem;
    color: #9CA3AF;
}

.notif-empty {
    text-align: center;
    padding: 3.5rem 1.5rem;
    color: #9CA3AF;
}
.notif-empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: .75rem;
}
.notif-empty h6 {
    font-size: .95rem;
    font-weight: 600;
    color: #6B7280;
    margin-bottom: .35rem;
}
.notif-empty p {
    font-size: .82rem;
    margin: 0;
}

.notif-pagination { margin-top: 1.25rem; }
</style>
@endpush
