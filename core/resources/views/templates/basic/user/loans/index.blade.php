@extends($activeTemplate . 'layouts.master')

@section('content')

@php
    $user           = auth()->user();
    $savingsBalance = $user->savings_wallet     ?? 0;
    $moneyBox       = $user->balance            ?? 0;
    $activeLoan     = $activeLoan               ?? null;
    $loanHistory    = $loanHistory              ?? collect();
    $schedule       = $schedule                 ?? collect();
    $minThreshold   = $minThreshold             ?? 100;
    $loanEligible   = $savingsBalance >= $minThreshold;
    $hasActiveLoan  = !is_null($activeLoan);

    $repaidPct = 0;
    if ($hasActiveLoan && $activeLoan->original_amount > 0) {
        $repaidPct = min(100, (($activeLoan->original_amount - $activeLoan->outstanding) / $activeLoan->original_amount) * 100);
    }
@endphp
<div class="nc-wrap" id="ncWrap">
    <br/>
{{-- ── Page header ─────────────────────────────────── --}}
<div class="sl-page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="sl-page-title mb-1">Loans</h4>
        <p class="sl-page-subtitle mb-0">Borrow against your savings. Repay automatically or manually.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('user.loans.history') }}" class="sl-btn sl-btn-outline">
            <i class="las la-history me-1"></i> Loan History
        </a>
        @if(!$hasActiveLoan && $loanEligible)
            <a href="{{ route('user.loans.apply') }}" class="sl-btn sl-btn-primary">
                <i class="las la-hand-holding-usd me-1"></i> Get Loan
            </a>
        @elseif($hasActiveLoan)
            <button class="sl-btn sl-btn-primary disabled" tabindex="-1"
                    data-bs-toggle="tooltip" title="Clear your active loan before applying for a new one."
                    aria-disabled="true">
                <i class="las la-hand-holding-usd me-1"></i> Get Loan
            </button>
        @else
            <button class="sl-btn sl-btn-primary disabled" tabindex="-1"
                    data-bs-toggle="tooltip"
                    title="Savings Wallet balance must be at least {{ showAmount($minThreshold) }} to apply."
                    aria-disabled="true">
                <i class="las la-hand-holding-usd me-1"></i> Get Loan
            </button>
        @endif
    </div>
</div>

{{-- ── Threshold warning ────────────────────────────── --}}
@if(!$loanEligible)
<div class="alert sl-alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="las la-exclamation-triangle fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>Savings threshold not met.</strong>
        Your Savings Wallet balance ({{ showAmount($savingsBalance) }}) is below the minimum
        {{ showAmount($minThreshold) }} required to access loans.
        Continue earning to build your savings and unlock loan access.
    </div>
</div>
@endif

{{-- ── Default restriction banner ───────────────────── --}}
@if($hasActiveLoan && ($activeLoan->status ?? '') === 'defaulted')
<div class="alert sl-alert-danger d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="las la-exclamation-circle fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>Loan in default.</strong>
        Your loan is overdue. All future earnings will be applied to repayment (after 10% mandatory savings) until this loan is cleared.
        Contact support if you need assistance.
        <a href="{{ route('ticket.index') }}" class="alert-link ms-1">Contact Support →</a>
    </div>
</div>
@endif

{{-- ── Wallet summary ───────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="sl-stat-card sl-stat-primary">
            <div class="sl-stat-icon"><i class="las la-piggy-bank"></i></div>
            <div class="sl-stat-body">
                <p class="sl-stat-label">Savings Wallet</p>
                <h3 class="sl-stat-value">{{ showAmount($savingsBalance) }}</h3>
                <p class="sl-stat-note">
                    @if($loanEligible)
                        <i class="las la-check-circle me-1"></i>Loan-eligible
                    @else
                        <i class="las la-lock me-1"></i>Below threshold
                    @endif
                </p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="sl-stat-card sl-stat-gold">
            <div class="sl-stat-icon"><i class="las la-wallet"></i></div>
            <div class="sl-stat-body">
                <p class="sl-stat-label">Money Box</p>
                <h3 class="sl-stat-value">{{ showAmount($moneyBox) }}</h3>
                <p class="sl-stat-note">Source for manual repayments</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="sl-stat-card sl-stat-info">
            <div class="sl-stat-icon"><i class="las la-file-invoice-dollar"></i></div>
            <div class="sl-stat-body">
                <p class="sl-stat-label">Outstanding Balance</p>
                <h3 class="sl-stat-value {{ $hasActiveLoan ? 'text-danger' : '' }}">
                    {{ $hasActiveLoan ? showAmount($activeLoan->outstanding) : showAmount(0) }}
                </h3>
                <p class="sl-stat-note">{{ $hasActiveLoan ? 'Active loan' : 'No active loan' }}</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="sl-stat-card sl-stat-success">
            <div class="sl-stat-icon"><i class="las la-calendar-check"></i></div>
            <div class="sl-stat-body">
                <p class="sl-stat-label">Next Due Date</p>
                <h3 class="sl-stat-value" style="font-size:1.1rem;">
                    {{ $hasActiveLoan && $activeLoan->next_due_date ? $activeLoan->next_due_date->format('M d, Y') : '—' }}
                </h3>
                <p class="sl-stat-note">{{ $hasActiveLoan ? 'Upcoming payment' : 'No payment due' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Active loan card ─────────────────────────────── --}}
@if($hasActiveLoan)
<div class="sl-card mb-4">
    <div class="sl-card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <h6 class="mb-0">Active Loan</h6>
            <span class="sl-status sl-status-{{ $activeLoan->status ?? 'active' }}">
                {{ ucfirst($activeLoan->status ?? 'active') }}
            </span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('user.loans.show', $activeLoan->id) }}" class="sl-btn sl-btn-sm sl-btn-outline">
                View Details
            </a>
            <button class="sl-btn sl-btn-sm sl-btn-primary" data-bs-toggle="modal" data-bs-target="#repayModal">
                <i class="las la-hand-holding-usd me-1"></i>Repay
            </button>
        </div>
    </div>
    <div class="sl-card-body">
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <p class="sl-mini-label">Loan Amount</p>
                <p class="sl-mini-val fw-700">{{ showAmount($activeLoan->original_amount ?? 0) }}</p>
            </div>
            <div class="col-6 col-md-3">
                <p class="sl-mini-label">Outstanding</p>
                <p class="sl-mini-val fw-700 text-danger">{{ showAmount($activeLoan->outstanding ?? 0) }}</p>
            </div>
            <div class="col-6 col-md-3">
                <p class="sl-mini-label">Total Repaid</p>
                <p class="sl-mini-val fw-700 text-success">{{ showAmount($activeLoan->total_repaid ?? 0) }}</p>
            </div>
            <div class="col-6 col-md-3">
                <p class="sl-mini-label">Interest Rate</p>
                <p class="sl-mini-val fw-700">{{ $activeLoan->interest_rate ?? '—' }}%</p>
            </div>
        </div>

        {{-- Repayment progress --}}
        <div class="mb-2 d-flex justify-content-between align-items-center">
            <small class="text-muted fw-600">Repayment Progress</small>
            <small class="fw-700 text-success">{{ round($repaidPct) }}% repaid</small>
        </div>
        <div class="progress sl-progress-lg" role="progressbar"
             aria-valuenow="{{ round($repaidPct) }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar sl-progress-bar-repay" style="width:{{ $repaidPct }}%"></div>
        </div>

    </div>
</div>

{{-- Repayment schedule --}}
@if(!$schedule->isEmpty())
<div class="sl-card mb-4">
    <div class="sl-card-header">
        Repayment Schedule
        <span class="sl-badge-count ms-auto">{{ $schedule->count() }} installments</span>
    </div>
    <div class="sl-card-body p-0">
        <div class="table-responsive">
            <table class="sl-table" aria-label="Repayment schedule">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Due Date</th>
                        <th>Principal</th>
                        <th>Interest</th>
                        <th>Total Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule as $i => $inst)
                    <tr class="{{ ($inst->status ?? '') === 'overdue' ? 'sl-row-overdue' : '' }}">
                        <td data-label="#">{{ $i + 1 }}</td>
                        <td data-label="Due Date">{{ $inst->due_date->format('M d, Y') }}</td>
                        <td data-label="Principal">{{ showAmount($inst->principal) }}</td>
                        <td data-label="Interest">{{ showAmount($inst->interest) }}</td>
                        <td data-label="Total Due" class="fw-600">{{ showAmount($inst->total) }}</td>
                        <td data-label="Status">
                            <span class="sl-status sl-status-{{ $inst->status ?? 'pending' }}">
                                {{ ucfirst($inst->status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@else
{{-- ── No active loan: onboarding CTA ─────────────── --}}
<div class="sl-card mb-4">
    <div class="sl-card-body">
        <div class="sl-onboard-state">
            <div class="sl-onboard-icon"><i class="las la-hand-holding-usd"></i></div>
            <h5 class="sl-onboard-title">No active loan</h5>
            <p class="sl-onboard-sub">
                You can borrow up to <strong>2× your Savings Wallet balance</strong>.
                Repay automatically from earnings or manually from your Money Box.
            </p>
            @if($loanEligible)
                <a href="{{ route('user.loans.apply') }}" class="sl-btn sl-btn-primary sl-btn-lg mt-2">
                    <i class="las la-hand-holding-usd me-2"></i>Apply for a Loan
                </a>
            @else
                <div class="sl-info-banner mt-3 text-start">
                    <i class="las la-lock me-1"></i>
                    Grow your Savings Wallet to at least <strong>{{ showAmount($minThreshold) }}</strong> to unlock loan access.
                </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Loan history ─────────────────────────────────── --}}
@if(!$loanHistory->isEmpty())
<div class="sl-card">
    <div class="sl-card-header">
        Loan History
        <span class="sl-badge-count ms-auto">{{ $loanHistory->count() }}</span>
    </div>
    <div class="sl-card-body p-0">
        <div class="table-responsive">
            <table class="sl-table" aria-label="Loan history">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Tenure</th>
                        <th>Interest</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loanHistory as $loan)
                    <tr>
                        <td data-label="Date">{{ $loan->created_at->format('M d, Y') }}</td>
                        <td data-label="Amount" class="fw-600">{{ showAmount($loan->original_amount) }}</td>
                        <td data-label="Tenure">{{ $loan->tenure_months }} months</td>
                        <td data-label="Interest">{{ $loan->interest_rate }}%</td>
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
    </div>
</div>
@endif

{{-- ── Manual Repayment Modal ────────────────────────── --}}
@push('modal')
@if($hasActiveLoan)
<div class="modal fade" id="repayModal" tabindex="-1" aria-labelledby="repayLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sl-modal">
            <div class="sl-modal-header">
                <h5 class="modal-title" id="repayLabel">
                    <i class="las la-hand-holding-usd me-2"></i>Manual Loan Repayment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.loans.repay', $activeLoan->id) }}" method="POST" class="disableSubmission">
                @csrf
                <div class="modal-body p-4">
                    <div class="sl-modal-balance-row mb-4">
                        <div>
                            <p class="sl-mini-label mb-0">Outstanding</p>
                            <p class="mb-0 fw-700 text-danger">{{ showAmount($activeLoan->outstanding ?? 0) }}</p>
                        </div>
                        <div class="text-end">
                            <p class="sl-mini-label mb-0">Money Box</p>
                            <p class="mb-0 fw-700 text-success">{{ showAmount($moneyBox) }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="sl-label" for="repay_amount">Repayment Amount <span class="sl-required">*</span></label>
                        <div class="sl-input-group">
                            <span class="sl-input-prefix">{{ gs('cur_sym') }}</span>
                            <input type="number" class="sl-input sl-input-has-prefix" id="repay_amount" name="amount"
                                   placeholder="0.00" step="0.01" min="1" max="{{ $moneyBox }}" required>
                        </div>
                        <p class="sl-field-hint">Max: <strong>{{ showAmount($moneyBox) }}</strong> (Money Box balance)</p>
                    </div>

                    <div class="mb-3">
                        <label class="sl-label">Source of Repayment</label>
                        <input class="sl-input" value="Money Box (only)" readonly disabled>
                    </div>

                    <div class="sl-info-banner mb-0">
                        <i class="las la-info-circle me-1"></i>
                        Only your <strong>Money Box</strong> can be used to repay loans.
                        Your Savings Wallet cannot be used for repayment.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="sl-btn sl-btn-outline flex-fill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sl-btn sl-btn-primary flex-fill" {{ $moneyBox <= 0 ? 'disabled' : '' }}>
                        <i class="las la-check me-1"></i>Confirm Repayment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endpush
</div>
@endsection

@push('style')
<style>
:root {
    --sl-green:#0D5C2E; --sl-green-lt:#E8F5EF;
    --sl-gold:#C8973A;  --sl-gold-lt:#FEF8EC;
    --sl-blue:#1D6FA4;  --sl-blue-lt:#EBF5FF;
    --sl-radius:14px; --sl-shadow:0 2px 16px rgba(0,0,0,.07); --sl-border:#E5E9EF;
}
.sl-page-title { font-size:1.35rem; font-weight:700; color:var(--bk-text); }
.sl-page-subtitle { font-size:.875rem; color:var(--bk-muted); }
.sl-btn { display:inline-flex; align-items:center; gap:.3rem; padding:.5rem 1.1rem; border-radius:8px; font-size:.875rem; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all .2s; }
.sl-btn-sm { padding:.35rem .8rem; font-size:.8rem; }
.sl-btn-lg { padding:.7rem 1.6rem; font-size:.95rem; }
.sl-btn-primary { background:var(--sl-green); color:#fff; }
.sl-btn-primary:hover:not([disabled]):not(.disabled) { background:#094422; color:#fff; transform:translateY(-1px); box-shadow:0 4px 14px rgba(13,92,46,.25); }
.sl-btn-outline { background:#fff; color:var(--sl-green); border:1.5px solid var(--sl-green); }
.sl-btn-outline:hover { background:var(--sl-green-lt); }
.sl-btn.disabled, .sl-btn[disabled] { opacity:.45; pointer-events:none; }

.sl-alert-warning { background:#FFFBEB; border:1px solid #FDE68A; border-radius:12px; color:#92400E; padding:1rem 1.25rem; }
.sl-alert-danger  { background:#FFF1F2; border:1px solid #FECDD3; border-radius:12px; color:#9F1239; padding:1rem 1.25rem; }

/* Stat cards */
.sl-stat-card { border-radius:var(--sl-radius); padding:1.25rem; display:flex; gap:1rem; align-items:flex-start; box-shadow:var(--sl-shadow); transition:transform .2s, box-shadow .2s; height:100%; }
.sl-stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 28px rgba(0,0,0,.1); }
.sl-stat-primary { background:linear-gradient(135deg, #0D5C2E, #16A34A); color:#fff; }
.sl-stat-gold    { background:linear-gradient(135deg, #92400E, #C8973A); color:#fff; }
.sl-stat-info    { background:var(--sl-blue-lt); color:var(--sl-blue); border:1px solid #BFDBFE; }
.sl-stat-success { background:var(--sl-green-lt); color:var(--sl-green); border:1px solid #BBF7D0; }
.sl-stat-icon    { font-size:1.6rem; opacity:.85; flex-shrink:0; }
.sl-stat-label   { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; opacity:.8; margin-bottom:.25rem; }
.sl-stat-value   { font-size:1.5rem; font-weight:800; margin-bottom:.2rem; }
.sl-stat-note    { font-size:.73rem; opacity:.75; margin-bottom:0; }

.sl-card { background:#fff; border-radius:var(--sl-radius); border:1px solid var(--sl-border); box-shadow:var(--sl-shadow); overflow:hidden; }
.sl-card-header { padding:1rem 1.25rem; border-bottom:1px solid var(--sl-border); font-size:.95rem; font-weight:700; color:var(--bk-text); display:flex; align-items:center; gap:.6rem; }
.sl-card-body { padding:1.25rem; }
.sl-badge-count { background:var(--sl-green-lt); color:var(--sl-green); font-size:.75rem; font-weight:700; padding:.2rem .65rem; border-radius:999px; }

.sl-mini-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--bk-muted); margin-bottom:.2rem; }
.sl-mini-val { font-size:1rem; margin-bottom:0; }

.sl-progress-lg { height:14px; border-radius:999px; background:#E5E9EF; }
.sl-progress-bar-repay { background:linear-gradient(90deg, #609f63, #4ed48a); border-radius:999px; transition:width .6s ease; }

/* Onboard state */
.sl-onboard-state { text-align:center; padding:3rem 2rem; }
.sl-onboard-icon  { font-size:3.5rem; color:#D1D5DB; margin-bottom:1rem; }
.sl-onboard-title { font-weight:700; font-size:1.15rem; color:var(--bk-text); margin-bottom:.5rem; }
.sl-onboard-sub   { font-size:.875rem; color:var(--bk-muted); max-width:380px; margin:0 auto 1rem; line-height:1.6; }

/* Table */
.sl-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.sl-table thead th { background:#F9FAFB; color:var(--bk-muted); font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:.75rem 1.1rem; border-bottom:1px solid var(--sl-border); white-space:nowrap; }
.sl-table tbody td { padding:.85rem 1.1rem; border-bottom:1px solid var(--sl-border); vertical-align:middle; color:var(--bk-text); }
.sl-table tbody tr:last-child td { border-bottom:0; }
.sl-table tbody tr:hover { background:#F9FAFB; }
.sl-row-overdue { background:#FFF1F2; }
.sl-row-overdue:hover { background:#FEE2E2 !important; }

.sl-status { font-size:.72rem; font-weight:700; padding:.28rem .6rem; border-radius:6px; }
.sl-status-active   { background:#DCFCE7; color:#15803D; }
.sl-status-cleared  { background:#DCFCE7; color:#15803D; }
.sl-status-paid     { background:#DCFCE7; color:#15803D; }
.sl-status-pending  { background:#FEF3C7; color:#92400E; }
.sl-status-overdue  { background:#FEE2E2; color:#DC2626; }
.sl-status-defaulted{ background:#FEE2E2; color:#DC2626; }
.sl-status-rejected { background:#F3F4F6; color:#6B7280; }

.sl-action-btn { font-size:.82rem; font-weight:600; color:var(--sl-green); text-decoration:none; }
.sl-action-btn:hover { text-decoration:underline; }

.fw-600 { font-weight:600; }
.fw-700 { font-weight:700; }

/* Modal */
.sl-modal { border-radius:16px; overflow:hidden; border:0; }
.sl-modal-header { display:flex; align-items:center; justify-content:space-between; padding:1.1rem 1.5rem; background:linear-gradient(135deg, #0D5C2E, #16A34A); color:#fff; }
.sl-modal-header .modal-title { font-size:1rem; font-weight:700; color:#fff; margin:0; }
.sl-modal-balance-row { display:flex; justify-content:space-between; background:#F9FAFB; border-radius:10px; padding:1rem; }

.sl-label { display:block; font-size:.82rem; font-weight:600; color:var(--bk-text); margin-bottom:.45rem; }
.sl-required { color:#DC2626; }
.sl-input { display:block; width:100%; padding:.6rem .85rem; font-size:.875rem; border:1.5px solid var(--sl-border); border-radius:9px; background:#fff; color:var(--bk-text); transition:border-color .2s, box-shadow .2s; }
.sl-input:focus { outline:none; border-color:var(--sl-green); box-shadow:0 0 0 3px rgba(13,92,46,.12); }
.sl-input:disabled { background:#F9FAFB; color:var(--bk-muted); }
.sl-input-group { position:relative; }
.sl-input-prefix { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:var(--bk-muted); font-size:.875rem; font-weight:600; pointer-events:none; z-index:1; }
.sl-input-has-prefix { padding-left:2.2rem; }
.sl-field-hint { font-size:.75rem; color:var(--bk-muted); margin-top:.35rem; margin-bottom:0; }
.sl-info-banner { background:var(--sl-blue-lt); border:1px solid #BFDBFE; border-radius:9px; padding:.75rem 1rem; font-size:.84rem; color:var(--sl-blue); }

@media (max-width: 767px) {
    .sl-table thead { display:none; }
    .sl-table, .sl-table tbody, .sl-table tr, .sl-table td { display:block; }
    .sl-table tr { border-bottom:2px solid var(--sl-border); }
    .sl-table td { border-bottom:0; padding:.4rem 1rem; font-size:.83rem; }
    .sl-table td::before { content:attr(data-label); display:inline-block; font-size:.7rem; font-weight:700; color:var(--bk-muted); text-transform:uppercase; letter-spacing:.05em; min-width:110px; }
}
</style>
@endpush

@push('script')
<script>
$(function () {
    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });
});
</script>
@endpush
