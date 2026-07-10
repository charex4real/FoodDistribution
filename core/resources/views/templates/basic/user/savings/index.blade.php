@extends($activeTemplate . 'layouts.master')
 
@section('content')

@php
    $user              = auth()->user();
    $savingsBalance    = $user->savings_wallet   ?? 0;   // aggregate locked in savings products
    $moneyBoxBalance   = $user->balance           ?? 0;   // liquid Money Box (source for new savings)
    $activeSavings     = $activeSavings          ?? collect();
    $matureCount       = $matureCount            ?? 0;
    $totalSaved        = $totalSaved             ?? 0;
    $totalInterest     = $totalInterest          ?? 0;
    $hasBalance        = $moneyBoxBalance > 0;            // gate on Money Box, not savings_wallet
@endphp
<div class="nc-wrap" id="ncWrap">
    <br/>
    {{-- ── Page Header ─────────────────────────────────── --}}
    <div class="sl-page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="sl-page-title mb-1">Savings</h4>
            <!-- <p class="sl-page-subtitle mb-0">Grow your wealth, one cycle at a time.</p> -->
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('user.savings.history') }}" class="sl-btn sl-btn-outline">
                <i class="las la-history me-1"></i> History
            </a>
            @if($hasBalance)
                <a href="{{ route('user.savings.create') }}" class="sl-btn sl-btn-primary">
                    <i class="las la-plus me-1"></i> Create Savings
                </a>
            @else
                <button class="sl-btn sl-btn-primary disabled" tabindex="-1"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        title="Your Money Box balance is empty. Deposit or earn funds first."
                        aria-disabled="true">
                    <i class="las la-plus me-1"></i> Create Savings
                </button>
            @endif
        </div>
    </div>

    {{-- ── Zero-balance alert ───────────────────────────── --}}
    @if(!$hasBalance)
    <div class="alert sl-alert-warning d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="las la-exclamation-circle fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <strong>Money Box is empty.</strong>
            You need funds in your Money Box to create a savings product.
            <a href="{{ route('user.deposit.index') }}" class="alert-link ms-1">Deposit funds →</a>
        </div>
    </div>
    @endif

    {{-- ── Maturity notification ────────────────────────── --}}
    @if($matureCount > 0)
    <div class="alert sl-alert-success d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="las la-check-circle fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <strong>{{ $matureCount }} savings product{{ $matureCount > 1 ? 's' : '' }} matured!</strong>
            Open each product to withdraw your principal and earned interest to your Money Box.
        </div>
    </div>
    @endif

    {{-- ── Wallet summary cards ─────────────────────────── --}}
    <div class="row g-3 mb-4">

        {{-- Savings Wallet --}}
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="sl-stat-card sl-stat-primary">
                <div class="sl-stat-icon"><i class="las la-piggy-bank"></i></div>
                <div class="sl-stat-body">
                    <p class="sl-stat-label">Total Locked in Savings</p>
                    <h3 class="sl-stat-value">{{ showAmount($savingsBalance) }}</h3>
                    <p class="sl-stat-note"><i class="las la-lock me-1"></i>Locked until each product matures</p>
                </div>
            </div>
        </div>

        {{-- Money Box --}}
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="sl-stat-card sl-stat-gold">
                <div class="sl-stat-icon"><i class="las la-wallet"></i></div>
                <div class="sl-stat-body">
                    <p class="sl-stat-label">Money Box</p>
                    <h3 class="sl-stat-value">{{ showAmount($moneyBoxBalance) }}</h3>
                    <!-- <p class="sl-stat-note">Available for loans &amp; fund subscriptions</p>  -->
                    <p class="sl-stat-note text-black">Available money</p>
                </div>
            </div>
        </div>

        {{-- Total Principal Saved --}}
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="sl-stat-card sl-stat-info">
                <div class="sl-stat-icon"><i class="las la-chart-bar"></i></div>
                <div class="sl-stat-body">
                    <p class="sl-stat-label">Total Principal Saved</p>
                    <h3 class="sl-stat-value">{{ showAmount($totalSaved) }}</h3>
                    <p class="sl-stat-note">Across all products</p>
                </div>
            </div>
        </div>

        {{-- Interest Earned --}}
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="sl-stat-card sl-stat-success">
                <div class="sl-stat-icon"><i class="las la-percentage"></i></div>
                <div class="sl-stat-body">
                    <p class="sl-stat-label">Interest Earned</p>
                    <h3 class="sl-stat-value">{{ showAmount($totalInterest) }}</h3>
                    <p class="sl-stat-note">Projected at maturity</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Savings product types ────────────────────────── --}}
    <h6 class="sl-section-label mb-3">Available Products</h6>
    <div class="row g-3 mb-5">

        {{-- Target Savings --}}
        <div class="col-12 col-md-4">
            <div class="sl-product-card">
                <div class="sl-product-icon sl-icon-green">
                    <i class="las la-bullseye"></i>
                </div>
                <h6 class="sl-product-name">Target Savings</h6>
                <p class="sl-product-desc">Set a goal. Save weekly or monthly. Earn interest at maturity.</p>
                <ul class="sl-product-features">
                    <li><i class="las la-check-circle text-success"></i> Flexible frequency</li>
                    <li><i class="las la-check-circle text-success"></i> Auto-contribution schedule</li>
                    <li><i class="las la-check-circle text-success"></i> Interest on maturity</li>
                </ul>
                @if($hasBalance)
                    <a href="{{ route('user.savings.create', ['type' => 'target']) }}" class="sl-product-btn">
                        Start Saving <i class="las la-arrow-right ms-1"></i>
                    </a>
                @else
                    <button class="sl-product-btn disabled" disabled>Fund wallet first</button>
                @endif
            </div>
        </div>

        {{-- Fixed Box --}}
        <div class="col-12 col-md-4">
            <div class="sl-product-card">
                <div class="sl-product-icon sl-icon-gold">
                    <i class="las la-box"></i>
                </div>
                <h6 class="sl-product-name">Fixed Box</h6>
                <p class="sl-product-desc">Lock a lump sum for a fixed term. Higher interest reward at maturity.</p>
                <ul class="sl-product-features">
                    <li><i class="las la-check-circle text-success"></i> Lump-sum deposit</li>
                    <li><i class="las la-check-circle text-success"></i> Fixed guaranteed interest</li>
                    <li><i class="las la-check-circle text-success"></i> No early withdrawal</li>
                </ul>
                @if($hasBalance)
                    <a href="{{ route('user.savings.create', ['type' => 'fixed']) }}" class="sl-product-btn">
                        Lock Funds <i class="las la-arrow-right ms-1"></i>
                    </a>
                @else
                    <button class="sl-product-btn disabled" disabled>Fund wallet first</button>
                @endif
            </div>
        </div>

        {{-- Farm Yield --}}
        <div class="col-12 col-md-4">
            <div class="sl-product-card sl-product-card-featured">
                <span class="sl-product-badge">Popular</span>
                <div class="sl-product-icon sl-icon-primary">
                    <i class="las la-seedling"></i>
                </div>
                <h6 class="sl-product-name">Farm Yield Savings</h6>
                <p class="sl-product-desc">Invest in active farm cycles. Earn yields when the cycle completes.</p>
                <ul class="sl-product-features">
                    <li><i class="las la-check-circle text-success"></i> Real agri-linked returns</li>
                    <li><i class="las la-check-circle text-success"></i> Cycle-based maturity</li>
                    <li><i class="las la-check-circle text-success"></i> Higher yield potential</li>
                </ul>
                @if($hasBalance)
                    <a href="{{ route('user.savings.create', ['type' => 'farm']) }}" class="sl-product-btn sl-product-btn-featured">
                        Join a Cycle <i class="las la-arrow-right ms-1"></i>
                    </a>
                @else
                    <button class="sl-product-btn sl-product-btn-featured disabled" disabled>Fund wallet first</button>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Active savings list ───────────────────────────── --}}
    <div class="sl-card mb-4">
        <div class="sl-card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">My Savings Products</h6>
            <span class="sl-badge-count">{{ $activeSavings->count() }} active</span>
        </div>
        <div class="sl-card-body p-0">
            @if($activeSavings->isEmpty())
                <div class="sl-empty-state">
                    <div class="sl-empty-icon"><i class="las la-piggy-bank"></i></div>
                    <p class="sl-empty-title">No savings products yet</p>
                    <p class="sl-empty-sub">Create your first savings product above to start growing your wealth.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="sl-table" aria-label="Active savings products">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Balance</th>
                                <th>Progress</th>
                                <th>Maturity</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeSavings as $saving)
                            <tr>
                                <td>
                                    <div class="sl-table-name">
                                        <div class="sl-table-avatar sl-icon-{{ $saving->type_color ?? 'green' }}">
                                            <i class="las la-{{ $saving->type_icon ?? 'piggy-bank' }}"></i>
                                        </div>
                                        <div>
                                            <p class="fw-600 mb-0">{{ $saving->name }}</p>
                                            <small class="text-muted">Started {{ $saving->created_at->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="sl-type-badge sl-type-{{ $saving->type }}">{{ ucfirst($saving->type) }}</span></td>
                                <td class="fw-600">{{ showAmount($saving->balance) }}</td>
                                <td style="min-width:140px">
                                    @php
                                        $pct = min(100, max(0, ($saving->balance / max($saving->target_amount, 1)) * 100));
                                    @endphp
                                    <div class="sl-progress-wrap">
                                        <div class="progress sl-progress" role="progressbar"
                                             aria-valuenow="{{ round($pct) }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar sl-progress-bar" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <small>{{ round($pct) }}%</small>
                                    </div>
                                </td>
                                <td>{{ $saving->maturity_date ? $saving->maturity_date->format('M d, Y') : '—' }}</td>
                                <td>
                                    @if($saving->status === 'matured')
                                        <span class="sl-status sl-status-matured">Matured</span>
                                    @elseif($saving->status === 'active')
                                        <span class="sl-status sl-status-active">Active</span>
                                    @elseif($saving->status === 'closed')
                                        <span class="sl-status sl-status-closed">Closed</span>
                                    @else
                                        <span class="sl-status sl-status-pending">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('user.savings.show', $saving->id) }}" class="sl-action-btn">
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
/* ── Savings & Loans shared design system ──────────── */

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
