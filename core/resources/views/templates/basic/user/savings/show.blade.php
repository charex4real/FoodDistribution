@extends($activeTemplate . 'layouts.master')

@section('content')

@php
    $user    = auth()->user();
    $saving  = $saving  ?? null;
    $txns    = $txns    ?? collect();
    $savingsBalance = $user->balance ?? 0;  // Money Box — source for adding funds

    $isMatured  = $saving && $saving->status === 'matured';
    $isActive   = $saving && $saving->status === 'active';
    $isClosed   = $saving && $saving->status === 'closed';

    // Farm savings: only allow adding funds while the linked cycle is open
    $farmCycleOpen = $saving && $saving->type === 'farm'
        ? optional($saving->farmCycle)->status === 'open'
        : true;

    // Target savings: block once the target amount has been reached
    $targetReached = $saving && $saving->type === 'target'
        && $saving->target_amount > 0
        && $saving->balance >= $saving->target_amount;

    $canAddFunds = $isActive && $farmCycleOpen && !$targetReached;
    $canWithdraw = $isMatured;

    $pct = $saving && $saving->target_amount > 0
        ? min(100, ($saving->balance / $saving->target_amount) * 100)
        : 100;
@endphp
<div class="nc-wrap" id="ncWrap">
    <br>
    {{-- ── Page header ─────────────────────────────────── --}}
    <div class="sl-page-header d-flex flex-wrap align-items-center gap-3 mb-4">
        <a href="{{ route('user.savings.index') }}" class="sl-back-btn" aria-label="Back">
            <i class="las la-arrow-left"></i>
        </a>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="sl-page-title mb-0">{{ $saving->name ?? 'Savings Product' }}</h4>
                @if($isMatured)
                    <span class="sl-status sl-status-matured">Matured</span>
                @elseif($isActive)
                    <span class="sl-status sl-status-active">Active</span>
                @elseif($isClosed)
                    <span class="sl-status sl-status-closed">Closed</span>
                @endif
            </div>
            <p class="sl-page-subtitle mb-0">
                {{ ucfirst($saving->type ?? '—') }} Savings ·
                Started {{ $saving->created_at->format('M d, Y') ?? '—' }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($canAddFunds)
                <button class="sl-btn sl-btn-outline" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                    <i class="las la-plus me-1"></i> Add Funds
                </button>
            @endif
            @if($canWithdraw)
                <button class="sl-btn sl-btn-success" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                    <i class="las la-arrow-circle-up me-1"></i> Withdraw
                </button>
            @endif
        </div>
    </div>

    {{-- ── Farm cycle status notices ────────────────────── --}}
    @if($saving->type === 'farm' && $isActive)
        @php $linkedCycle = $saving->farmCycle; @endphp
        @if($linkedCycle && $linkedCycle->status === 'closed')
        <div class="alert sl-alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
            <i class="las la-lock fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <strong>Farm cycle closed.</strong>
                The <strong>{{ $linkedCycle->name }}</strong> cycle is no longer accepting new contributions.
                Your existing savings will earn yield until the maturity date
                ({{ $linkedCycle->maturity_date?->format('M d, Y') ?? '—' }}).
            </div>
        </div>
        @endif
    @endif

    {{-- ── Target reached notice ──────────────────────── --}}
    @if($targetReached && $isActive)
    <div class="alert sl-alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="las la-check-circle fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <strong>Savings target reached!</strong>
            You have saved the full target of <strong>{{ showAmount($saving->target_amount) }}</strong>.
            No additional funds can be added. Your savings will continue to earn interest until maturity.
        </div>
    </div>
    @endif

    {{-- ── Maturity call-to-action banner ─────────────── --}}
    @if($isMatured)
    <div class="alert sl-alert-success d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="las la-check-circle fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <strong>This savings product has matured!</strong>
            Your principal plus {{ showAmount($saving->interest_earned ?? 0) }} interest is ready.
            Click <strong>Withdraw</strong> to release funds to your Money Box.
        </div>
    </div>
    @endif

    <div class="row g-4">

        {{-- ── LEFT: Summary + transactions ───────────── --}}
        <div class="col-12 col-lg-8">

            {{-- Balance summary --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="sl-mini-stat">
                        <p class="sl-mini-label">Balance</p>
                        <p class="sl-mini-value text-success">{{ showAmount($saving->balance ?? 0) }}</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sl-mini-stat">
                        <p class="sl-mini-label">Principal</p>
                        <p class="sl-mini-value">{{ showAmount($saving->principal ?? 0) }}</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sl-mini-stat">
                        <p class="sl-mini-label">Interest</p>
                        <p class="sl-mini-value text-success">+{{ showAmount($saving->interest_earned ?? 0) }}</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sl-mini-stat">
                        <p class="sl-mini-label">Maturity</p>
                        <p class="sl-mini-value" style="font-size:.85rem;">
                            {{ $saving->maturity_date ? $saving->maturity_date->format('M d, Y') : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Progress bar (Target / Fixed) --}}
            @if(in_array($saving->type ?? '', ['target', 'fixed']))
            <div class="sl-card mb-4">
                <div class="sl-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="fw-700 mb-0">Savings Progress</p>
                            <small class="text-muted">
                                {{ showAmount($saving->balance ?? 0) }} of
                                {{ showAmount($saving->target_amount ?? 0) }}
                            </small>
                        </div>
                        <span class="sl-pct-badge">{{ round($pct) }}%</span>
                    </div>
                    <div class="progress sl-progress-lg" role="progressbar"
                         aria-valuenow="{{ round($pct) }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar sl-progress-bar-lg"
                             style="width:{{ $pct }}%">
                        </div>
                    </div>
                    @if($saving->type === 'target' && $saving->frequency)
                    <div class="d-flex gap-4 mt-3 flex-wrap">
                        <div>
                            <small class="text-muted d-block">Frequency</small>
                            <span class="fw-600">{{ ucfirst($saving->frequency) }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Per Cycle</small>
                            <span class="fw-600">{{ showAmount($saving->contribution_per_cycle ?? 0) }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Cycles Left</small>
                            <span class="fw-600">{{ $saving->cycles_remaining ?? '—' }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Next Due</small>
                            <span class="fw-600">{{ $saving->next_due ? $saving->next_due->format('M d') : '—' }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Contribution schedule (Target) --}}
            @if(($saving->type ?? '') === 'target' && !$txns->isEmpty())
            <div class="sl-card mb-4">
                <div class="sl-card-header">
                    Contribution History
                    <span class="sl-badge-count ms-auto">{{ $txns->count() }} transactions</span>
                </div>
                <div class="sl-card-body p-0">
                    <div class="table-responsive">
                        <table class="sl-table" aria-label="Transaction history">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($txns as $txn)
                                <tr>
                                    <td data-label="Date">{{ $txn->created_at->format('M d, Y') }}</td>
                                    <td data-label="Description">{{ $txn->description }}</td>
                                    <td data-label="Amount" class="{{ $txn->type === 'credit' ? 'text-success' : 'text-danger' }} fw-600">
                                        {{ $txn->type === 'credit' ? '+' : '-' }}{{ showAmount($txn->amount) }}
                                    </td>
                                    <td data-label="Status">
                                        <span class="sl-status sl-status-{{ $txn->status }}">{{ ucfirst($txn->status) }}</span>
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

        {{-- ── RIGHT: info panel ───────────────────────── --}}
        <div class="col-12 col-lg-4">

            <div class="sl-card mb-3">
                <div class="sl-card-header">Product Details</div>
                <div class="sl-card-body">
                    <div class="sl-detail-row">
                        <span>Type</span>
                        <span class="sl-type-badge sl-type-{{ $saving->type ?? 'target' }}">{{ ucfirst($saving->type ?? '—') }}</span>
                    </div>
                    <div class="sl-detail-row">
                        <span>Status</span>
                        <span class="sl-status sl-status-{{ $saving->status ?? 'active' }}">{{ ucfirst($saving->status ?? '—') }}</span>
                    </div>
                    <div class="sl-detail-row">
                        <span>Created</span>
                        <strong>{{ $saving->created_at->format('M d, Y') ?? '—' }}</strong>
                    </div>
                    <div class="sl-detail-row">
                        <span>Maturity Date</span>
                        <strong>{{ $saving->maturity_date ? $saving->maturity_date->format('M d, Y') : '—' }}</strong>
                    </div>
                    @if($saving->type === 'farm')
                    <div class="sl-detail-row">
                        <span>Farm Cycle</span>
                        <strong>{{ $saving->farmCycle->name ?? '—' }}</strong>
                    </div>
                    <div class="sl-detail-row">
                        <span>Cycle Status</span>
                        <strong>{{ ucfirst($saving->farmCycle->status ?? '—') }}</strong>
                    </div>
                    @endif
                    <div class="sl-detail-row">
                        <span>Interest Rate</span>
                        <strong class="text-success">{{ $saving->interest_rate ?? '—' }}% p.a.</strong>
                    </div>
                </div>
            </div>

            {{-- Restrictions card --}}
            <div class="sl-card sl-card-info mb-3">
                <div class="sl-card-body">
                    <p class="fw-700 mb-2" style="font-size:.88rem;"><i class="las la-info-circle me-1"></i>Restrictions</p>
                    <ul class="sl-restriction-list">
                        <li><i class="las la-times-circle text-danger"></i>No early withdrawal</li>
                        <li><i class="las la-times-circle text-danger"></i>Cannot fund from Money Box</li>
                        @if($isClosed || $isMatured)
                            <li><i class="las la-times-circle text-danger"></i>Cannot add more funds</li>
                        @else
                            <li><i class="las la-check-circle text-success"></i>Can add funds from Savings Wallet</li>
                        @endif
                        @if($isMatured)
                            <li><i class="las la-check-circle text-success"></i>Ready to withdraw to Money Box</li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Add Funds Modal ──────────────────────────────── --}}
    @push('modal')
    <div class="modal fade" id="addFundsModal" tabindex="-1" aria-labelledby="addFundsLabel" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sl-modal">
                <div class="sl-modal-header">
                    <h5 class="modal-title" id="addFundsLabel">
                        <i class="las la-plus-circle me-2"></i>Add Funds to Savings
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('user.savings.add_funds', $saving->id ?? 0) }}" method="POST" class="disableSubmission">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="sl-modal-balance-row mb-4">
                            <div>
                                <p class="sl-mini-label mb-0">Current Balance</p>
                                <p class="sl-mini-value mb-0">{{ showAmount($saving->balance ?? 0) }}</p>
                            </div>
                            <div class="text-end">
                                <p class="sl-mini-label mb-0">Money Box (available)</p>
                                <p class="sl-mini-value mb-0 text-success">{{ showAmount($savingsBalance) }}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="sl-label" for="add_amount">Amount to Add <span class="sl-required">*</span></label>
                            <div class="sl-input-group">
                                <span class="sl-input-prefix">{{ gs('cur_sym') }}</span>
                                <input type="number" class="sl-input sl-input-has-prefix" id="add_amount" name="amount"
                                       placeholder="0.00" step="0.01" min="1" max="{{ $savingsBalance }}" required>
                            </div>
                            <p class="sl-field-hint">Max: <strong>{{ showAmount($savingsBalance) }}</strong> (Money Box balance)</p>
                        </div>
                        <div class="sl-info-banner mb-0">
                            <i class="las la-lock me-1"></i>
                            Funds will be deducted from your <strong>Money Box</strong> and locked in this product until maturity.
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="sl-btn sl-btn-outline flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="sl-btn sl-btn-primary flex-fill" {{ $savingsBalance <= 0 ? 'disabled' : '' }}>
                            <i class="las la-check me-1"></i>Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Withdraw Modal ───────────────────────────────── --}}
    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawLabel" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sl-modal">
                <div class="sl-modal-header sl-modal-header-success">
                    <h5 class="modal-title" id="withdrawLabel">
                        <i class="las la-arrow-circle-up me-2"></i>Withdraw Matured Savings
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('user.savings.withdraw', $saving->id ?? 0) }}" method="POST" class="disableSubmission">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="sl-withdraw-summary mb-4">
                            <div class="sl-withdraw-row">
                                <span>Principal</span>
                                <strong>{{ showAmount($saving->principal ?? 0) }}</strong>
                            </div>
                            <div class="sl-withdraw-row">
                                <span>Interest Earned</span>
                                <strong class="text-success">+{{ showAmount($saving->interest_earned ?? 0) }}</strong>
                            </div>
                            <div class="sl-withdraw-total">
                                <span>Total Release</span>
                                <strong>{{ showAmount(($saving->principal ?? 0) + ($saving->interest_earned ?? 0)) }}</strong>
                            </div>
                        </div>
                        <div class="sl-info-banner mb-0">
                            <i class="las la-info-circle me-1"></i>
                            Funds will be credited to your <strong>Money Box</strong> and this savings product will be closed.
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="sl-btn sl-btn-outline flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="sl-btn sl-btn-success flex-fill">
                            <i class="las la-arrow-circle-up me-1"></i>Withdraw to Money Box
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
.sl-btn-success { background:#15803D; color:#fff; }
.sl-btn-success:hover { background:#14532D; color:#fff; }
.sl-btn[disabled] { opacity:.45; cursor:not-allowed; }

.sl-alert-success { background:#F0FDF4; border:1px solid #BBF7D0; border-radius:12px; color:#14532D; padding:1rem 1.25rem; }
.sl-alert-warning { background:#FFFBEB; border:1px solid #FDE68A; border-radius:12px; color:#78350F; padding:1rem 1.25rem; }

.sl-card { background:#fff; border-radius:var(--sl-radius); border:1px solid var(--sl-border); box-shadow:var(--sl-shadow); overflow:hidden; }
.sl-card-info { background:var(--sl-blue-lt); border-color:#BFDBFE; }
.sl-card-header { padding:1rem 1.25rem; border-bottom:1px solid var(--sl-border); font-size:.95rem; font-weight:700; color:var(--bk-text); display:flex; align-items:center; gap:.6rem; }
.sl-card-body { padding:1.25rem; }
.sl-badge-count { background:var(--sl-green-lt); color:var(--sl-green); font-size:.75rem; font-weight:700; padding:.2rem .65rem; border-radius:999px; }

/* Mini stats */
.sl-mini-stat { background:#fff; border-radius:12px; border:1px solid var(--sl-border); padding:1rem; text-align:center; box-shadow:var(--sl-shadow); }
.sl-mini-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--bk-muted); margin-bottom:.3rem; }
.sl-mini-value { font-size:1.05rem; font-weight:700; color:var(--bk-text); margin-bottom:0; }

/* Progress */
.sl-progress-lg { height:14px; border-radius:999px; background:#E5E9EF; }
.sl-progress-bar-lg { background:linear-gradient(90deg, #16A34A, #0D5C2E); border-radius:999px; transition:width .6s ease; }
.sl-pct-badge { background:var(--sl-green); color:#fff; font-size:.75rem; font-weight:700; padding:.3rem .7rem; border-radius:999px; }

/* Detail rows */
.sl-detail-row { display:flex; justify-content:space-between; align-items:center; padding:.55rem 0; border-bottom:1px solid #F3F4F6; font-size:.86rem; color:var(--bk-muted); }
.sl-detail-row:last-child { border-bottom:0; }
.sl-detail-row strong { color:var(--bk-text); }

.sl-restriction-list { list-style:none; padding:0; margin:0; font-size:.83rem; color:var(--bk-text); }
.sl-restriction-list li { display:flex; align-items:center; gap:.5rem; margin-bottom:.4rem; }

/* Table */
.sl-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.sl-table thead th { background:#F9FAFB; color:var(--bk-muted); font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:.75rem 1.1rem; border-bottom:1px solid var(--sl-border); }
.sl-table tbody td { padding:.85rem 1.1rem; border-bottom:1px solid var(--sl-border); vertical-align:middle; color:var(--bk-text); }
.sl-table tbody tr:last-child td { border-bottom:0; }
.sl-table tbody tr:hover { background:#F9FAFB; }

/* Status & type badges */
.sl-status { font-size:.72rem; font-weight:700; padding:.28rem .6rem; border-radius:6px; }
.sl-status-active  { background:#DCFCE7; color:#15803D; }
.sl-status-matured { background:#FEF9C3; color:#854D0E; }
.sl-status-closed  { background:#F3F4F6; color:#6B7280; }
.sl-status-pending { background:#FEF3C7; color:#92400E; }
.sl-status-completed { background:#DCFCE7; color:#15803D; }
.sl-status-failed  { background:#FEE2E2; color:#DC2626; }
.sl-type-badge { font-size:.72rem; font-weight:700; padding:.25rem .55rem; border-radius:6px; }
.sl-type-target { background:#EFF6FF; color:#1D4ED8; }
.sl-type-fixed  { background:var(--sl-gold-lt); color:var(--sl-gold); }
.sl-type-farm   { background:var(--sl-green-lt); color:var(--sl-green); }

.fw-600 { font-weight:600; }
.fw-700 { font-weight:700; }

/* Modal */
.sl-modal { border-radius:16px; overflow:hidden; border:0; }
.sl-modal-header { display:flex; align-items:center; justify-content:space-between; padding:1.1rem 1.5rem; background:linear-gradient(135deg, #0D5C2E, #16A34A); color:#fff; }
.sl-modal-header-success { background:linear-gradient(135deg, #14532D, #15803D); }
.sl-modal-header .modal-title { font-size:1rem; font-weight:700; color:#fff; margin:0; }
.sl-modal-balance-row { display:flex; justify-content:space-between; background:#F9FAFB; border-radius:10px; padding:1rem; }

/* Withdraw summary */
.sl-withdraw-summary { background:#F9FAFB; border-radius:12px; overflow:hidden; }
.sl-withdraw-row { display:flex; justify-content:space-between; padding:.65rem 1rem; border-bottom:1px solid var(--sl-border); font-size:.9rem; color:var(--bk-muted); }
.sl-withdraw-total { display:flex; justify-content:space-between; padding:.75rem 1rem; background:var(--sl-green-lt); font-size:.95rem; font-weight:700; color:var(--sl-green); }

.sl-info-banner { background:var(--sl-blue-lt); border:1px solid #BFDBFE; border-radius:9px; padding:.75rem 1rem; font-size:.84rem; color:var(--sl-blue); }
.sl-label { display:block; font-size:.82rem; font-weight:600; color:var(--bk-text); margin-bottom:.45rem; }
.sl-required { color:#DC2626; }
.sl-input { display:block; width:100%; padding:.6rem .85rem; font-size:.875rem; border:1.5px solid var(--sl-border); border-radius:9px; background:#fff; color:var(--bk-text); transition:border-color .2s, box-shadow .2s; }
.sl-input:focus { outline:none; border-color:var(--sl-green); box-shadow:0 0 0 3px rgba(13,92,46,.12); }
.sl-input-group { position:relative; }
.sl-input-prefix { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:var(--bk-muted); font-size:.875rem; font-weight:600; pointer-events:none; z-index:1; }
.sl-input-has-prefix { padding-left:2.2rem; }
.sl-field-hint { font-size:.75rem; color:var(--bk-muted); margin-top:.35rem; margin-bottom:0; }

@media (max-width: 767px) {
    .sl-table thead { display:none; }
    .sl-table, .sl-table tbody, .sl-table tr, .sl-table td { display:block; }
    .sl-table tr { border-bottom:2px solid var(--sl-border); }
    .sl-table td { border-bottom:0; padding:.4rem 1rem; font-size:.83rem; }
    .sl-table td::before { content: attr(data-label); display:inline-block; font-size:.7rem; font-weight:700; color:var(--bk-muted); text-transform:uppercase; letter-spacing:.05em; min-width:110px; }
}
</style>
@endpush
