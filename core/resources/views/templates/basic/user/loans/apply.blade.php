@extends($activeTemplate . 'layouts.master')

@section('content')

@php
    use App\Constants\Status;
    $user           = auth()->user();
    $savingsBalance = $user->savings_wallet ?? 0;
    $moneyBox       = $user->balance        ?? 0;
    $loanProducts   = $loanProducts         ?? collect();
    $minThreshold   = $minThreshold         ?? 100;
    $maxMultiple    = $maxMultiple          ?? 2;
    $maxLoanable    = $savingsBalance * $maxMultiple;

    $checks = [
        'kyc'         => $user->kv == Status::KYC_VERIFIED,
        'membership'  => $membershipOk  ?? true,
        'threshold'   => $savingsBalance >= $minThreshold,
        'no_default'  => $noDefault      ?? true,
        'no_active'   => $noActiveLoan   ?? true,
    ];
    $allChecksPass = !in_array(false, $checks, true);
@endphp

{{-- ── KYC gate banner ────────────────────────────────── --}}
@if(!$checks['kyc'])
<div style="background:{{ $user->kv == Status::KYC_PENDING ? 'linear-gradient(135deg,#FFFBEB,#FEF3C7)' : 'linear-gradient(135deg,#FEF2F2,#FEE2E2)' }};border:1.5px solid {{ $user->kv == Status::KYC_PENDING ? '#FCD34D' : '#FECACA' }};border-radius:14px;padding:18px 22px;margin-bottom:24px;display:flex;gap:14px;align-items:flex-start;">
    <div style="width:40px;height:40px;flex-shrink:0;background:{{ $user->kv == Status::KYC_PENDING ? '#FEF3C7' : '#FEE2E2' }};border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;color:{{ $user->kv == Status::KYC_PENDING ? '#D97706' : '#DC2626' }};">
        <i class="las la-{{ $user->kv == Status::KYC_PENDING ? 'hourglass-half' : 'shield-alt' }}"></i>
    </div>
    <div>
        <p style="font-size:.9rem;font-weight:700;color:{{ $user->kv == Status::KYC_PENDING ? '#92400E' : '#991B1B' }};margin:0 0 4px;">
            {{ $user->kv == Status::KYC_PENDING ? __('KYC Under Review') : __('KYC Verification Required') }}
        </p>
        <p style="font-size:.82rem;color:{{ $user->kv == Status::KYC_PENDING ? '#B45309' : '#B91C1C' }};margin:0;">
            @if($user->kv == Status::KYC_PENDING)
                Your identity documents are being reviewed. Loan applications will be available once an admin verifies your KYC.
            @else
                You must complete KYC verification before applying for a loan.
                <a href="{{ route('user.kyc.form') }}" style="color:#059669;font-weight:700;text-decoration:underline;">Verify now &rarr;</a>
            @endif
        </p>
    </div>
</div>
@endif

{{-- ── Page header ─────────────────────────────────── --}}
<div class="sl-page-header d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('user.loans.index') }}" class="sl-back-btn" aria-label="Back">
        <i class="las la-arrow-left"></i>
    </a>
    <div>
        <h4 class="sl-page-title mb-0">Loan Application</h4>
        <p class="sl-page-subtitle mb-0">Choose a product and confirm eligibility before applying.</p>
    </div>
</div>

<div class="row g-4">

    {{-- ── LEFT: Eligibility + form ────────────────── --}}
    <div class="col-12 col-lg-7">

        {{-- Eligibility checklist --}}
        <div class="sl-card mb-4">
            <div class="sl-card-header">
                <span class="sl-step-num">1</span> Eligibility Check
                @if($allChecksPass)
                    <span class="ms-auto sl-status sl-status-active">All clear</span>
                @else
                    <span class="ms-auto sl-status sl-status-overdue">Action required</span>
                @endif
            </div>
            <div class="sl-card-body">
                <div class="sl-check-list">
                    <div class="sl-check-item {{ $checks['kyc'] ? 'ok' : 'fail' }}">
                        <div class="sl-check-icon">
                            <i class="las la-{{ $checks['kyc'] ? 'check' : 'times' }}-circle"></i>
                        </div>
                        <div class="sl-check-text">
                            <p class="fw-600 mb-0">KYC Verification</p>
                            <small>
                                @if($checks['kyc'])
                                    Identity verified — you are eligible to apply.
                                @elseif($user->kv == Status::KYC_PENDING)
                                    Your KYC is under review. Please wait for admin approval.
                                @else
                                    <a href="{{ route('user.kyc.form') }}" style="color:#059669;font-weight:600;">Complete KYC verification</a> — required before applying for a loan.
                                @endif
                            </small>
                        </div>
                    </div>
                    <div class="sl-check-item {{ $checks['membership'] ? 'ok' : 'fail' }}">
                        <div class="sl-check-icon">
                            <i class="las la-{{ $checks['membership'] ? 'check' : 'times' }}-circle"></i>
                        </div>
                        <div class="sl-check-text">
                            <p class="fw-600 mb-0">Membership Duration</p>
                            <small>{{ $checks['membership'] ? 'Meets minimum membership period' : 'Must be a member for at least 30 days' }}</small>
                        </div>
                    </div>
                    <div class="sl-check-item {{ $checks['threshold'] ? 'ok' : 'fail' }}">
                        <div class="sl-check-icon">
                            <i class="las la-{{ $checks['threshold'] ? 'check' : 'times' }}-circle"></i>
                        </div>
                        <div class="sl-check-text">
                            <p class="fw-600 mb-0">Savings Wallet Threshold</p>
                            <small>
                                @if($checks['threshold'])
                                    Balance {{ showAmount($savingsBalance) }} meets the {{ showAmount($minThreshold) }} minimum.
                                @else
                                    Balance {{ showAmount($savingsBalance) }} is below {{ showAmount($minThreshold) }} minimum.
                                @endif
                            </small>
                        </div>
                    </div>
                    <div class="sl-check-item {{ $checks['no_default'] ? 'ok' : 'fail' }}">
                        <div class="sl-check-icon">
                            <i class="las la-{{ $checks['no_default'] ? 'check' : 'times' }}-circle"></i>
                        </div>
                        <div class="sl-check-text">
                            <p class="fw-600 mb-0">No Default History</p>
                            <small>{{ $checks['no_default'] ? 'No active loan defaults on record' : 'Active default detected — clear it first' }}</small>
                        </div>
                    </div>
                    <div class="sl-check-item {{ $checks['no_active'] ? 'ok' : 'fail' }}">
                        <div class="sl-check-icon">
                            <i class="las la-{{ $checks['no_active'] ? 'check' : 'times' }}-circle"></i>
                        </div>
                        <div class="sl-check-text">
                            <p class="fw-600 mb-0">No Active Loan</p>
                            <small>{{ $checks['no_active'] ? 'You have no current outstanding loan' : 'Clear your existing loan before applying' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Product selection --}}
        <div class="sl-card mb-4">
            <div class="sl-card-header">
                <span class="sl-step-num">2</span> Choose Loan Product
            </div>
            <div class="sl-card-body">
                @if($loanProducts->isEmpty())
                    <div class="sl-empty-state py-3">
                        <div class="sl-empty-icon" style="font-size:2rem"><i class="las la-file-invoice-dollar"></i></div>
                        <p class="sl-empty-title" style="font-size:.9rem">No loan products available</p>
                        <p class="sl-empty-sub">Check back later for available loan products.</p>
                    </div>
                @else
                    <div class="row g-3" id="loanProductPicker">
                        @foreach($loanProducts as $product)
                        <div class="col-12 col-sm-6">
                            <label class="sl-loan-product-card" for="product_{{ $product->id }}">
                                <input type="radio" name="selected_product" id="product_{{ $product->id }}"
                                       value="{{ $product->id }}"
                                       data-max="{{ $product->max_loan_amount ?? 999999 }}"
                                       data-multiple="{{ $product->savings_multiple }}"
                                       data-rate="{{ $product->interest_rate }}"
                                       data-tenures="{{ implode(',', $product->tenure_options ?? [3,6,12]) }}">
                                <div class="sl-lp-header">
                                    <div class="sl-lp-icon">
                                        <i class="las la-hand-holding-usd"></i>
                                    </div>
                                    <div>
                                        <p class="fw-700 mb-0">{{ $product->name }}</p>
                                        <small class="text-muted">Up to {{ $product->savings_multiple }}× your savings</small>
                                    </div>
                                </div>
                                <div class="sl-lp-stats">
                                    <div>
                                        <p class="sl-mini-label mb-0">Max Amount</p>
                                        <p class="fw-700 mb-0 text-success">{{ showAmount(min($product->max_loan_amount ?? $maxLoanable, $maxLoanable)) }}</p>
                                    </div>
                                    <div class="text-end">
                                        <p class="sl-mini-label mb-0">Interest</p>
                                        <p class="fw-700 mb-0">{{ $product->interest_rate }}% p.a.</p>
                                    </div>
                                </div>
                                <div class="sl-lp-tenures">
                                    @foreach(($product->tenure_options ?? [3, 6, 12]) as $t)
                                        <span class="sl-tenure-chip">{{ $t }}m</span>
                                    @endforeach
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Step 3: Loan request form --}}
        <div class="sl-card mb-4" id="loanFormPanel">
            <div class="sl-card-header">
                <span class="sl-step-num">3</span> Loan Details
            </div>
            <div class="sl-card-body">
                <form action="{{ route('user.loans.store') }}" method="POST" id="loanForm" class="disableSubmission">
                    @csrf
                    <input type="hidden" name="loan_product_id" id="loan_product_id">

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="sl-label" for="amount">Desired Amount <span class="sl-required">*</span></label>
                            <div class="sl-input-group">
                                <span class="sl-input-prefix">{{ gs('cur_sym') }}</span>
                                <input type="number" class="sl-input sl-input-has-prefix @error('amount') is-invalid @enderror"
                                       id="amount" name="amount" placeholder="0.00"
                                       step="0.01" min="100" value="{{ old('amount') }}" required>
                            </div>
                            <p class="sl-field-hint" id="amountHint">Select a loan product first</p>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="sl-label" for="tenure">Repayment Tenure <span class="sl-required">*</span></label>
                            <select class="sl-select @error('tenure') is-invalid @enderror"
                                    id="tenure" name="tenure" required>
                                <option value="">Select product first…</option>
                            </select>
                            @error('tenure')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="sl-label" for="loan_purpose">Purpose (optional)</label>
                            <input type="text" class="sl-input" id="loan_purpose" name="purpose"
                                   placeholder="e.g. Business expansion, Emergency, Education"
                                   maxlength="120" value="{{ old('purpose') }}">
                        </div>
                    </div>

                    <div class="sl-info-banner mt-4">
                        <i class="las la-info-circle me-1"></i>
                        Upon approval, funds will be credited to your <strong>Money Box</strong>.
                        Repayment happens automatically from earnings (after 10% mandatory savings)
                        or you can repay manually from your Money Box.
                    </div>

                    <div class="sl-form-actions mt-4">
                        <a href="{{ route('user.loans.index') }}" class="sl-btn sl-btn-outline">Cancel</a>
                        <button type="submit" class="sl-btn sl-btn-primary"
                                id="submitLoanBtn"
                                {{ !$allChecksPass ? 'disabled' : '' }}>
                            <span class="sl-btn-text"><i class="las la-paper-plane me-1"></i>Submit Application</span>
                            <span class="sl-btn-loading d-none"><i class="las la-spinner la-spin me-1"></i>Submitting…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- /col-lg-7 --}}

    {{-- ── RIGHT: Loan summary preview ────────────── --}}
    <div class="col-12 col-lg-5">

        {{-- Your collateral info --}}
        <div class="sl-card mb-3">
            <div class="sl-card-header">Your Borrowing Power</div>
            <div class="sl-card-body">
                <div class="sl-bp-row">
                    <span>Savings Wallet Balance</span>
                    <strong>{{ showAmount($savingsBalance) }}</strong>
                </div>
                <div class="sl-bp-row">
                    <span>Loan Multiple</span>
                    <strong>Up to {{ $maxMultiple }}×</strong>
                </div>
                <div class="sl-bp-row sl-bp-highlight">
                    <span>Maximum You Can Borrow</span>
                    <strong class="text-success" id="borrowPowerDisplay">{{ showAmount($maxLoanable) }}</strong>
                </div>
            </div>
        </div>

        {{-- Live repayment summary --}}
        <div class="sl-preview-card sticky-lg-top" style="top:100px;">
            <div class="sl-preview-header">
                <i class="las la-calculator me-2"></i>Loan Summary
            </div>
            <div class="sl-preview-body">
                <div class="sl-preview-placeholder" id="loanPreviewPlaceholder">
                    <i class="las la-hand-holding-usd"></i>
                    <p>Select a product and enter<br>an amount to preview costs.</p>
                </div>
                <div id="loanPreviewContent" style="display:none;">
                    <div class="sl-preview-row">
                        <span>Loan Amount</span>
                        <strong id="lpv_amount">—</strong>
                    </div>
                    <div class="sl-preview-row">
                        <span>Interest Rate</span>
                        <strong id="lpv_rate">—</strong>
                    </div>
                    <div class="sl-preview-row">
                        <span>Tenure</span>
                        <strong id="lpv_tenure">—</strong>
                    </div>
                    <div class="sl-preview-row">
                        <span>Total Interest</span>
                        <strong id="lpv_interest" class="text-danger">—</strong>
                    </div>
                    <div class="sl-preview-divider"></div>
                    <div class="sl-preview-row sl-preview-total">
                        <span>Total Repayable</span>
                        <strong id="lpv_total">—</strong>
                    </div>
                    <div class="sl-preview-divider"></div>
                    <div class="sl-preview-row">
                        <span>Monthly Installment</span>
                        <strong id="lpv_monthly" class="text-primary">—</strong>
                    </div>
                    <div class="sl-preview-row">
                        <span>Start Date</span>
                        <strong id="lpv_start">—</strong>
                    </div>
                    <div class="sl-preview-row">
                        <span>Est. End Date</span>
                        <strong id="lpv_end">—</strong>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@push('style')
<style>

</style>
@endpush

@push('script')
<script>
$(function () {
    'use strict';

    var CUR          = '{{ gs("cur_sym") }}';
    var SAVINGS      = {{ $savingsBalance }};
    var MAX_MULTIPLE = {{ $maxMultiple }};
    var selectedRate    = 0;
    var selectedMax     = 0;
    var selectedTenures = [];

    function fmt(n) {
        return Number(n).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Parse a tenure token like "2w" or "6m" (or legacy "6") into { value, unit }
    function parseTenure(t) {
        t = (t + '').trim().toLowerCase();
        var m = t.match(/^(\d+)(w|m)$/);
        if (m) {
            return { value: parseInt(m[1]), unit: m[2] === 'w' ? 'week' : 'month', raw: t };
        }
        // Legacy bare integer: treat as months
        return { value: parseInt(t) || 0, unit: 'month', raw: t + 'm' };
    }

    function tenureLabel(p) {
        return p.value + ' ' + p.unit + (p.value > 1 ? 's' : '');
    }

    /* ── Product picker ──────────────────────────── */
    $('input[name="selected_product"]').on('change', function () {
        selectedRate    = parseFloat($(this).data('rate'))     || 0;
        selectedMax     = parseFloat($(this).data('max'))      || 0;
        var multiple    = parseFloat($(this).data('multiple')) || MAX_MULTIPLE;
        selectedTenures = ($(this).data('tenures') + '').split(',').map(function (t) { return t.trim(); });

        var maxBorrow = Math.min(selectedMax, SAVINGS * multiple);

        $('#amount').attr('max', maxBorrow);
        $('#amountHint').text('Max: ' + CUR + fmt(maxBorrow) + ' (based on your Savings Wallet)');
        $('#loan_product_id').val($(this).val());

        var $tenure = $('#tenure');
        $tenure.html('<option value="">Select tenure…</option>');

        $.each(selectedTenures, function (i, t) {
            var p = parseTenure(t);
            $tenure.append($('<option>', { value: p.raw, text: tenureLabel(p) }));
        });

        $('#borrowPowerDisplay').text(CUR + fmt(maxBorrow));
        recalcLoan();
    });

    /* ── Form inputs → live preview ─────────────── */
    $('#amount, #tenure').on('input change', recalcLoan);

    function recalcLoan() {
        var amount     = parseFloat($('#amount').val()) || 0;
        var tenureStr  = $('#tenure').val();
        var p          = parseTenure(tenureStr || '0m');

        if (!amount || !p.value || !selectedRate) { hideLoanPreview(); return; }

        // Interest scaled to actual period in years
        var years    = p.unit === 'week' ? (p.value * 7) / 365 : p.value / 12;
        var interest = amount * (selectedRate / 100) * years;
        var total    = amount + interest;
        var instAmt  = total / p.value;

        var start = new Date();
        var end   = new Date();
        if (p.unit === 'week') {
            end.setDate(end.getDate() + p.value * 7);
        } else {
            end.setMonth(end.getMonth() + p.value);
        }

        var dateFmt     = { day: 'numeric', month: 'short', year: 'numeric' };
        var instPeriod  = p.unit === 'week' ? '/ week' : '/ month';

        $('#lpv_amount').text(CUR + fmt(amount));
        $('#lpv_rate').text(selectedRate + '% p.a.');
        $('#lpv_tenure').text(tenureLabel(p));
        $('#lpv_interest').text('+' + CUR + fmt(interest));
        $('#lpv_total').text(CUR + fmt(total));
        $('#lpv_monthly').text(CUR + fmt(instAmt) + ' ' + instPeriod);
        $('#lpv_start').text(start.toLocaleDateString('en-GB', dateFmt));
        $('#lpv_end').text(end.toLocaleDateString('en-GB', dateFmt));

        $('#loanPreviewPlaceholder').hide();
        $('#loanPreviewContent').show();
    }

    function hideLoanPreview() {
        $('#loanPreviewPlaceholder').show();
        $('#loanPreviewContent').hide();
    }

    /* ── Loading state on submit ─────────────────── */
    $('#loanForm').on('submit', function () {
        var $btn     = $('#submitLoanBtn');
        var $btnText = $(this).find('.sl-btn-text');
        var $btnLoad = $(this).find('.sl-btn-loading');
        if (!$btn.prop('disabled') && $btnText.length && $btnLoad.length) {
            $btn.prop('disabled', true);
            $btnText.addClass('d-none');
            $btnLoad.removeClass('d-none');
        }
    });
});
</script>
@endpush
