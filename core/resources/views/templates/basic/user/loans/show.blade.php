@extends($activeTemplate . 'layouts.master')

@section('content')

@php
    $user       = auth()->user();
    $loan       = $loan       ?? null;
    $schedule   = $schedule   ?? collect();
    $repayments = $repayments ?? collect();
    $moneyBox   = $user->balance   ?? 0;

    $isActive   = in_array($loan->status ?? '', ['active', 'approved', 'at_risk', 'defaulted']);
    $isCleared  = ($loan->status ?? '') === 'cleared';

    $repaidPct  = 0;
    if ($loan && $loan->original_amount > 0) {
        $repaidPct = min(100, (($loan->original_amount - $loan->outstanding) / $loan->original_amount) * 100);
    }

    $statusColor = match($loan->status ?? '') {
        'active', 'approved' => 'active',
        'cleared'            => 'cleared',
        'at_risk'            => 'atrisk',
        'defaulted'          => 'defaulted',
        'pending'            => 'pending',
        default              => 'pending',
    };
@endphp
<div class="nc-wrap" id="ncWrap">
    <br/>
{{-- ── Page header ─────────────────────────────────── --}}
<div class="sl-page-header d-flex flex-wrap align-items-center gap-3 mb-4">
    <a href="{{ route('user.loans.index') }}" class="sl-back-btn" aria-label="Back">
        <i class="las la-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h4 class="sl-page-title mb-0">Loan #{{ $loan->reference ?? $loan->id ?? '—' }}</h4>
            <span class="sl-status sl-status-{{ $statusColor }}">{{ ucfirst($loan->status ?? '—') }}</span>
        </div>
        @php
            $tuUnit  = $loan->tenure_unit ?? 'month';
            $tuCount = $loan->tenure_months ?? 0;
            $tuLabel = $tuCount . ' ' . ($tuUnit === 'week' ? 'week' : 'month') . ($tuCount != 1 ? 's' : '');
        @endphp
        <p class="sl-page-subtitle mb-0">
            Applied {{ $loan->created_at->format('M d, Y') ?? '—' }} ·
            Tenure: {{ $tuLabel }}
        </p>
    </div>
    @if($isActive)
    <button class="sl-btn sl-btn-primary" data-bs-toggle="modal" data-bs-target="#repayModal">
        <i class="las la-hand-holding-usd me-1"></i> Repay Loan
    </button>
    @endif
</div>

{{-- ── At-risk warning ─────────────────────────────── --}}
@if(($loan->status ?? '') === 'at_risk')
<div class="alert sl-alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="las la-exclamation-triangle fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>Loan is at risk.</strong>
        A payment is overdue. All future earnings will be directed 100% to loan repayment
        (after mandatory 10% savings deduction) until the loan is brought current.
    </div>
</div>
@endif

@if(($loan->status ?? '') === 'defaulted')
<div class="alert sl-alert-danger d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="las la-exclamation-circle fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>Loan is in default.</strong>
        Extended non-payment has triggered account restrictions. You cannot withdraw from Money Box,
        subscribe to new funds, or apply for new loans until this is resolved.
        <a href="{{ route('ticket.index') }}" class="alert-link ms-1">Contact Support →</a>
    </div>
</div>
@endif

@if($isCleared)
<div class="alert sl-alert-success d-flex align-items-start gap-3 mb-4" role="alert">
    <i class="las la-check-circle fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>Loan fully repaid!</strong>
        Congratulations — this loan has been cleared. You are now eligible to apply for a new loan
        subject to your Savings Wallet balance threshold.
        <a href="{{ route('user.loans.apply') }}" class="alert-link ms-1">Apply for a New Loan →</a>
    </div>
</div>
@endif

<div class="row g-4">

    {{-- ── LEFT: Loan details + schedule ──────────── --}}
    <div class="col-12 col-lg-8">

        {{-- Balance summary row --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="sl-mini-stat">
                    <p class="sl-mini-label">Loan Amount</p>
                    <p class="sl-mini-value">{{ showAmount($loan->original_amount ?? 0) }}</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sl-mini-stat">
                    <p class="sl-mini-label">Outstanding</p>
                    <p class="sl-mini-value {{ $isActive ? 'text-danger' : '' }}">{{ showAmount($loan->outstanding ?? 0) }}</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sl-mini-stat">
                    <p class="sl-mini-label">Total Repaid</p>
                    <p class="sl-mini-value text-success">{{ showAmount($loan->total_repaid ?? 0) }}</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sl-mini-stat">
                    <p class="sl-mini-label">Next Due</p>
                    <p class="sl-mini-value" style="font-size:.85rem;">
                        {{ $isActive && $loan->next_due_date ? $loan->next_due_date->format('M d, Y') : ($isCleared ? 'Cleared' : '—') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Repayment progress --}}
        <div class="sl-card mb-4">
            <div class="sl-card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="fw-700 mb-0">Repayment Progress</p>
                    <span class="sl-pct-badge {{ $isCleared ? 'sl-pct-cleared' : '' }}">{{ round($repaidPct) }}%</span>
                </div>
                <div class="progress sl-progress-lg" role="progressbar"
                     aria-valuenow="{{ round($repaidPct) }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar sl-progress-bar-repay {{ $isCleared ? 'sl-bar-cleared' : '' }}"
                         style="width:{{ $repaidPct }}%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">{{ showAmount($loan->total_repaid ?? 0) }} repaid</small>
                    <small class="text-muted">{{ showAmount($loan->outstanding ?? 0) }} remaining</small>
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
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedule as $i => $inst)
                            <tr class="{{ in_array($inst->status ?? '', ['overdue', 'defaulted']) ? 'sl-row-overdue' : '' }}
                                       {{ ($inst->status ?? '') === 'paid' ? 'sl-row-paid' : '' }}">
                                <td data-label="#">{{ $i + 1 }}</td>
                                <td data-label="Due Date">{{ $inst->due_date->format('M d, Y') }}</td>
                                <td data-label="Principal">{{ showAmount($inst->principal) }}</td>
                                <td data-label="Interest">{{ showAmount($inst->interest) }}</td>
                                <td data-label="Total" class="fw-600">{{ showAmount($inst->total) }}</td>
                                <td data-label="Paid" class="{{ ($inst->amount_paid ?? 0) > 0 ? 'text-success' : '' }}">
                                    {{ showAmount($inst->amount_paid ?? 0) }}
                                </td>
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

        {{-- Repayment transaction log --}}
        @if(!$repayments->isEmpty())
        <div class="sl-card">
            <div class="sl-card-header">
                Payment History
                <span class="sl-badge-count ms-auto">{{ $repayments->count() }} payments</span>
            </div>
            <div class="sl-card-body p-0">
                <div class="table-responsive">
                    <table class="sl-table" aria-label="Payment history">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Source</th>
                                <th>Reference</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repayments as $repay)
                            <tr>
                                <td data-label="Date">{{ $repay->created_at->format('M d, Y H:i') }}</td>
                                <td data-label="Amount" class="fw-600 text-success">{{ showAmount($repay->amount) }}</td>
                                <td data-label="Source">
                                    <span class="sl-source-badge sl-source-{{ $repay->source ?? 'manual' }}">
                                        {{ ucfirst($repay->source ?? 'manual') }}
                                    </span>
                                </td>
                                <td data-label="Reference">
                                    <code class="sl-ref-code">{{ $repay->reference ?? '—' }}</code>
                                </td>
                                <td data-label="Status">
                                    <span class="sl-status sl-status-{{ $repay->status }}">{{ ucfirst($repay->status) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /col-lg-8 --}}

    {{-- ── RIGHT: Loan info + rules ───────────────── --}}
    <div class="col-12 col-lg-4">

        <div class="sl-card mb-3">
            <div class="sl-card-header">Loan Details</div>
            <div class="sl-card-body">
                <div class="sl-detail-row">
                    <span>Reference</span>
                    <code class="sl-ref-code">{{ $loan->reference ?? '—' }}</code>
                </div>
                <div class="sl-detail-row">
                    <span>Product</span>
                    <strong>{{ $loan->product->name ?? '—' }}</strong>
                </div>
                <div class="sl-detail-row">
                    <span>Applied On</span>
                    <strong>{{ $loan->created_at->format('M d, Y') ?? '—' }}</strong>
                </div>
                <div class="sl-detail-row">
                    <span>Approved On</span>
                    <strong>{{ $loan->approved_at ? $loan->approved_at->format('M d, Y') : 'Pending' }}</strong>
                </div>
                <div class="sl-detail-row">
                    <span>Disbursed To</span>
                    <strong>Money Box</strong>
                </div>
                <div class="sl-detail-row">
                    <span>Tenure</span>
                    <strong>{{ $tuLabel }}</strong>
                </div>
                <div class="sl-detail-row">
                    <span>Interest Rate</span>
                    <strong class="text-danger">{{ $loan->interest_rate ?? '—' }}% p.a.</strong>
                </div>
                <div class="sl-detail-row">
                    <span>Total Interest</span>
                    <strong>{{ showAmount($loan->total_interest ?? 0) }}</strong>
                </div>
                <div class="sl-detail-row">
                    <span>Total Repayable</span>
                    <strong>{{ showAmount(($loan->original_amount ?? 0) + ($loan->total_interest ?? 0)) }}</strong>
                </div>
                @if($loan->purpose)
                <div class="sl-detail-row">
                    <span>Purpose</span>
                    <strong>{{ $loan->purpose }}</strong>
                </div>
                @endif
            </div>
        </div>

        {{-- Repayment rules card --}}
        <div class="sl-card sl-card-info mb-3">
            <div class="sl-card-body">
                <p class="fw-700 mb-2" style="font-size:.88rem;"><i class="las la-info-circle me-1"></i>Repayment Rules</p>
                <ul class="sl-restriction-list">
                    <li><i class="las la-check-circle text-success"></i>Auto-deducted from earnings after 10% savings</li>
                    <li><i class="las la-check-circle text-success"></i>Manual repayment from Money Box allowed</li>
                    <li><i class="las la-times-circle text-danger"></i>Cannot use Savings Wallet to repay</li>
                    <li><i class="las la-times-circle text-danger"></i>Cannot apply for new loan while active</li>
                </ul>
            </div>
        </div>

        @if($isActive)
        {{-- Quick repay widget --}}
        <div class="sl-card">
            <div class="sl-card-header">Quick Repayment</div>
            <div class="sl-card-body">
                <div class="sl-quick-repay-balances mb-3">
                    <div>
                        <p class="sl-mini-label mb-0">Outstanding</p>
                        <p class="fw-700 text-danger mb-0">{{ showAmount($loan->outstanding ?? 0) }}</p>
                    </div>
                    <div class="text-end">
                        <p class="sl-mini-label mb-0">Money Box</p>
                        <p class="fw-700 text-success mb-0">{{ showAmount($moneyBox) }}</p>
                    </div>
                </div>
                <button class="sl-btn sl-btn-primary w-100" data-bs-toggle="modal" data-bs-target="#repayModal"
                        {{ $moneyBox <= 0 ? 'disabled' : '' }}>
                    <i class="las la-hand-holding-usd me-1"></i>
                    {{ $moneyBox <= 0 ? 'Insufficient Money Box' : 'Make a Payment' }}
                </button>
                @if($moneyBox <= 0)
                <p class="sl-field-hint text-center mt-2">Earn or deposit to fund your Money Box.</p>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── Repay Modal ──────────────────────────────────── --}}
@push('modal')
@if($isActive)
<div class="modal fade" id="repayModal" tabindex="-1" aria-labelledby="repayLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sl-modal">
            <div class="sl-modal-header">
                <h5 class="modal-title" id="repayLabel">
                    <i class="las la-hand-holding-usd me-2"></i>Loan Repayment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.loans.repay', $loan->id ?? 0) }}" method="POST" class="disableSubmission">
                @csrf
                <div class="modal-body p-4">

                    {{-- Outstanding + Money Box summary --}}
                    <div class="sl-repay-summary mb-4">
                        <div class="sl-repay-row">
                            <span>Outstanding Balance</span>
                            <strong class="text-danger">{{ showAmount($loan->outstanding ?? 0) }}</strong>
                        </div>
                        <div class="sl-repay-row">
                            <span>Next Installment</span>
                            <strong>{{ showAmount($loan->next_installment ?? 0) }}</strong>
                        </div>
                        <div class="sl-repay-row sl-repay-highlight">
                            <span>Money Box (available)</span>
                            <strong class="text-success">{{ showAmount($moneyBox) }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="sl-label" for="repay_amount">Repayment Amount <span class="sl-required">*</span></label>
                        <div class="sl-input-group">
                            <span class="sl-input-prefix">{{ gs('cur_sym') }}</span>
                            <input type="number" class="sl-input sl-input-has-prefix" id="repay_amount" name="amount"
                                   placeholder="0.00" step="0.01" min="1"
                                   max="{{ min($moneyBox, $loan->outstanding ?? 0) }}" required>
                        </div>
                        <p class="sl-field-hint">
                            Max: <strong>{{ showAmount($moneyBox) }}</strong>
                            <span class="ms-2 text-muted">|</span>
                            Outstanding: <strong>{{ showAmount($loan->outstanding ?? 0) }}</strong>
                        </p>
                    </div>

                    {{-- Quick fill buttons --}}
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        @if($loan->next_installment ?? 0)
                        <button type="button" class="sl-quick-fill-btn"
                                onclick="document.getElementById('repay_amount').value={{ $loan->next_installment ?? 0 }}">
                            Pay Installment
                        </button>
                        @endif
                        <button type="button" class="sl-quick-fill-btn"
                                onclick="document.getElementById('repay_amount').value={{ min($moneyBox, $loan->outstanding ?? 0) }}">
                            Pay Full Balance
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="sl-label">Repayment Source</label>
                        <input class="sl-input" value="Money Box (only)" readonly disabled>
                    </div>

                    <div class="sl-info-banner mb-0">
                        <i class="las la-info-circle me-1"></i>
                        Repayments deduct from your <strong>Money Box</strong> only.
                        Savings Wallet cannot be used. If you pay in full, the loan will be automatically <strong>closed</strong>.
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
.sl-back-btn { display:flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:#fff; border:1.5px solid var(--sl-border); color:var(--bk-text); text-decoration:none; font-size:1.1rem; transition:background .2s; flex-shrink:0; }
.sl-back-btn:hover { background:var(--sl-green-lt); color:var(--sl-green); }
.sl-btn { display:inline-flex; align-items:center; gap:.3rem; padding:.5rem 1.1rem; border-radius:8px; font-size:.875rem; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all .2s; }
.sl-btn-primary { background:var(--sl-green); color:#fff; }
.sl-btn-primary:hover:not([disabled]) { background:#094422; color:#fff; }
.sl-btn-outline { background:#fff; color:var(--sl-green); border:1.5px solid var(--sl-green); }
.sl-btn-outline:hover { background:var(--sl-green-lt); }
.sl-btn[disabled] { opacity:.45; cursor:not-allowed; }
.w-100 { width:100%; justify-content:center; }

.sl-alert-warning { background:#FFFBEB; border:1px solid #FDE68A; border-radius:12px; color:#92400E; padding:1rem 1.25rem; }
.sl-alert-danger  { background:#FFF1F2; border:1px solid #FECDD3; border-radius:12px; color:#9F1239; padding:1rem 1.25rem; }
.sl-alert-success { background:#F0FDF4; border:1px solid #BBF7D0; border-radius:12px; color:#14532D; padding:1rem 1.25rem; }

.sl-card { background:#fff; border-radius:var(--sl-radius); border:1px solid var(--sl-border); box-shadow:var(--sl-shadow); overflow:hidden; }
.sl-card-info { background:var(--sl-blue-lt); border-color:#BFDBFE; }
.sl-card-header { padding:1rem 1.25rem; border-bottom:1px solid var(--sl-border); font-size:.95rem; font-weight:700; color:var(--bk-text); display:flex; align-items:center; gap:.6rem; }
.sl-card-body { padding:1.25rem; }
.sl-badge-count { background:var(--sl-green-lt); color:var(--sl-green); font-size:.75rem; font-weight:700; padding:.2rem .65rem; border-radius:999px; }

.sl-mini-stat { background:#fff; border-radius:12px; border:1px solid var(--sl-border); padding:1rem; text-align:center; box-shadow:var(--sl-shadow); }
.sl-mini-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--bk-muted); margin-bottom:.3rem; }
.sl-mini-value { font-size:1.05rem; font-weight:700; color:var(--bk-text); margin-bottom:0; }

.sl-progress-lg { height:14px; border-radius:999px; background:#E5E9EF; }
.sl-progress-bar-repay { background:linear-gradient(90deg, #57d434, #9fd094); border-radius:999px; transition:width .6s ease; }
.sl-bar-cleared { background:linear-gradient(90deg, #16A34A, #0D5C2E); }
.sl-pct-badge { background:var(--sl-green); color:#fff; font-size:.75rem; font-weight:700; padding:.3rem .7rem; border-radius:999px; }
.sl-pct-cleared { background:var(--sl-green); }

.sl-detail-row { display:flex; justify-content:space-between; align-items:center; padding:.55rem 0; border-bottom:1px solid #F3F4F6; font-size:.86rem; color:var(--bk-muted); }
.sl-detail-row:last-child { border-bottom:0; }
.sl-detail-row strong { color:var(--bk-text); text-align:right; max-width:60%; }

.sl-restriction-list { list-style:none; padding:0; margin:0; font-size:.83rem; }
.sl-restriction-list li { display:flex; align-items:center; gap:.5rem; margin-bottom:.4rem; }

.sl-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.sl-table thead th { background:#F9FAFB; color:var(--bk-muted); font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:.75rem 1.1rem; border-bottom:1px solid var(--sl-border); white-space:nowrap; }
.sl-table tbody td { padding:.85rem 1.1rem; border-bottom:1px solid var(--sl-border); vertical-align:middle; }
.sl-table tbody tr:last-child td { border-bottom:0; }
.sl-table tbody tr:hover { background:#F9FAFB; }
.sl-row-overdue { background:#FFF1F2; }
.sl-row-overdue:hover { background:#FEE2E2 !important; }
.sl-row-paid { background:#F0FDF4; }
.sl-row-paid:hover { background:#DCFCE7 !important; }

.sl-status { font-size:.72rem; font-weight:700; padding:.28rem .6rem; border-radius:6px; }
.sl-status-active    { background:#DCFCE7; color:#15803D; }
.sl-status-cleared   { background:#DCFCE7; color:#15803D; }
.sl-status-paid      { background:#DCFCE7; color:#15803D; }
.sl-status-approved  { background:#DCFCE7; color:#15803D; }
.sl-status-pending   { background:#FEF3C7; color:#92400E; }
.sl-status-atrisk    { background:#FEF3C7; color:#92400E; }
.sl-status-at_risk   { background:#FEF3C7; color:#92400E; }
.sl-status-overdue   { background:#FEE2E2; color:#DC2626; }
.sl-status-defaulted { background:#FEE2E2; color:#DC2626; }

.sl-source-badge { font-size:.72rem; font-weight:700; padding:.2rem .5rem; border-radius:6px; }
.sl-source-manual    { background:#EFF6FF; color:#1D4ED8; }
.sl-source-automatic { background:var(--sl-green-lt); color:var(--sl-green); }

.sl-ref-code { font-family:monospace; font-size:.78rem; background:#F3F4F6; padding:.1rem .4rem; border-radius:4px; color:var(--bk-text); }

.sl-quick-repay-balances { display:flex; justify-content:space-between; background:#F9FAFB; border-radius:10px; padding:1rem; }

/* Modal */
.sl-modal { border-radius:16px; overflow:hidden; border:0; }
.sl-modal-header { display:flex; align-items:center; justify-content:space-between; padding:1.1rem 1.5rem; background:linear-gradient(135deg, #0D5C2E, #16A34A); color:#fff; }
.sl-modal-header .modal-title { font-size:1rem; font-weight:700; color:#fff; margin:0; }
.sl-repay-summary { background:#F9FAFB; border-radius:12px; overflow:hidden; }
.sl-repay-row { display:flex; justify-content:space-between; padding:.65rem 1rem; border-bottom:1px solid var(--sl-border); font-size:.88rem; color:var(--bk-muted); }
.sl-repay-highlight { display:flex; justify-content:space-between; padding:.7rem 1rem; background:var(--sl-green-lt); font-size:.88rem; color:var(--sl-green); font-weight:600; }

.sl-quick-fill-btn { padding:.35rem .85rem; border:1.5px solid var(--sl-border); border-radius:7px; font-size:.78rem; font-weight:600; cursor:pointer; background:#fff; color:var(--bk-text); transition:all .2s; }
.sl-quick-fill-btn:hover { border-color:var(--sl-green); color:var(--sl-green); background:var(--sl-green-lt); }

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

.fw-600 { font-weight:600; }
.fw-700 { font-weight:700; }

@media (max-width:767px) {
    .sl-table thead { display:none; }
    .sl-table, .sl-table tbody, .sl-table tr, .sl-table td { display:block; }
    .sl-table tr { border-bottom:2px solid var(--sl-border); }
    .sl-table td { border-bottom:0; padding:.4rem 1rem; font-size:.83rem; }
    .sl-table td::before { content:attr(data-label); display:inline-block; font-size:.7rem; font-weight:700; color:var(--bk-muted); text-transform:uppercase; letter-spacing:.05em; min-width:110px; }
}
</style>
@endpush
