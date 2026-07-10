@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="nc-wrap" id="ncWrap">
    <br/>
<div class="sl-page-header d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('user.loans.index') }}" class="sl-back-btn" aria-label="Back">
        <i class="las la-arrow-left"></i>
    </a>
    <div>
        <h4 class="sl-page-title mb-0">Loan History</h4>
        <p class="sl-page-subtitle mb-0">All past and cleared loan records.</p>
    </div>
</div>

<div class="sl-card">
    <div class="sl-card-header">
        Past Loans
        <span class="sl-badge-count ms-auto">{{ $loanHistory->count() }}</span>
    </div>
    <div class="sl-card-body p-0">
        @if($loanHistory->isEmpty())
            <div class="sl-empty-state">
                <div class="sl-empty-icon"><i class="las la-hand-holding-usd"></i></div>
                <p class="sl-empty-title">No loan history yet</p>
                <p class="sl-empty-sub">Cleared and rejected loans will appear here once you start borrowing.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="sl-table" aria-label="Loan history">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Tenure</th>
                            <th>Interest</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loanHistory as $loan)
                        <tr>
                            <td data-label="Reference">
                                <code class="sl-ref-code">{{ $loan->reference }}</code>
                            </td>
                            <td data-label="Amount" class="fw-600">{{ showAmount($loan->original_amount) }}</td>
                            <td data-label="Tenure">{{ $loan->tenure_months }}m</td>
                            <td data-label="Interest">{{ $loan->interest_rate }}%</td>
                            <td data-label="Applied On">{{ $loan->created_at->format('M d, Y') }}</td>
                            <td data-label="Status">
                                <span class="sl-status sl-status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
                            </td>
                            <td data-label="Action">
                                <a href="{{ route('user.loans.show', $loan->id) }}" class="sl-action-btn">
                                    View <i class="las la-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
</div>
@endsection

@push('style')
<style>
:root { --sl-green:#0D5C2E; --sl-green-lt:#E8F5EF; --sl-radius:14px; --sl-shadow:0 2px 16px rgba(0,0,0,.07); --sl-border:#E5E9EF; }
.sl-page-title { font-size:1.35rem; font-weight:700; color:var(--bk-text); }
.sl-page-subtitle { font-size:.875rem; color:var(--bk-muted); }
.sl-back-btn { display:flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:#fff; border:1.5px solid var(--sl-border); color:var(--bk-text); text-decoration:none; font-size:1.1rem; transition:background .2s; flex-shrink:0; }
.sl-back-btn:hover { background:var(--sl-green-lt); color:var(--sl-green); }
.sl-card { background:#fff; border-radius:var(--sl-radius); border:1px solid var(--sl-border); box-shadow:var(--sl-shadow); overflow:hidden; }
.sl-card-header { padding:1rem 1.25rem; border-bottom:1px solid var(--sl-border); font-size:.95rem; font-weight:700; color:var(--bk-text); display:flex; align-items:center; gap:.6rem; }
.sl-badge-count { background:var(--sl-green-lt); color:var(--sl-green); font-size:.75rem; font-weight:700; padding:.2rem .65rem; border-radius:999px; }
.sl-empty-state { text-align:center; padding:3.5rem 2rem; }
.sl-empty-icon { font-size:3rem; color:#D1D5DB; margin-bottom:.75rem; }
.sl-empty-title { font-weight:700; color:var(--bk-text); margin-bottom:.35rem; }
.sl-empty-sub { font-size:.85rem; color:var(--bk-muted); max-width:320px; margin:0 auto; }
.sl-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.sl-table thead th { background:#F9FAFB; color:var(--bk-muted); font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:.75rem 1.1rem; border-bottom:1px solid var(--sl-border); }
.sl-table tbody td { padding:.85rem 1.1rem; border-bottom:1px solid var(--sl-border); vertical-align:middle; }
.sl-table tbody tr:last-child td { border-bottom:0; }
.sl-table tbody tr:hover { background:#F9FAFB; }
.sl-status { font-size:.72rem; font-weight:700; padding:.28rem .6rem; border-radius:6px; }
.sl-status-active   { background:#DCFCE7; color:#15803D; }
.sl-status-cleared  { background:#DCFCE7; color:#15803D; }
.sl-status-pending  { background:#FEF3C7; color:#92400E; }
.sl-status-defaulted{ background:#FEE2E2; color:#DC2626; }
.sl-status-rejected { background:#F3F4F6; color:#6B7280; }
.sl-ref-code { font-family:monospace; font-size:.78rem; background:#F3F4F6; padding:.1rem .4rem; border-radius:4px; }
.sl-action-btn { font-size:.82rem; font-weight:600; color:var(--sl-green); text-decoration:none; }
.sl-action-btn:hover { text-decoration:underline; }
.fw-600 { font-weight:600; }
@media (max-width:767px) {
    .sl-table thead { display:none; }
    .sl-table, .sl-table tbody, .sl-table tr, .sl-table td { display:block; }
    .sl-table tr { border-bottom:2px solid var(--sl-border); }
    .sl-table td { border-bottom:0; padding:.4rem 1rem; font-size:.83rem; }
    .sl-table td::before { content:attr(data-label); display:inline-block; font-size:.7rem; font-weight:700; color:var(--bk-muted); text-transform:uppercase; min-width:110px; }
}
</style>
@endpush
