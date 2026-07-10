@extends($activeTemplate . 'layouts.master')

@section('content')

@php
    $user            = auth()->user();
    $savingsBalance  = $user->balance ?? 0;
    $selectedType    = request('type', 'target');
    $farmCycles      = $farmCycles      ?? collect();
    $ss              = $savingsSettings ?? collect();

    // Type availability (admin-controlled via is_active)
    $targetEnabled   = $ss->has('target');
    $fixedEnabled    = $ss->has('fixed_1') || $ss->has('fixed_3') || $ss->has('fixed_6') || $ss->has('fixed_12');
    $farmEnabled     = $ss->has('farm');

    // Pull settings per type (with safe defaults)
    $targetRate      = optional($ss->get('target'))->interest_rate      ?? 8;
    $targetMin       = optional($ss->get('target'))->min_amount         ?? 100;

    $fixed1Rate      = optional($ss->get('fixed_1'))->interest_rate     ?? 8;
    $fixed1Min       = optional($ss->get('fixed_1'))->min_amount        ?? 500;

    $fixed3Rate      = optional($ss->get('fixed_3'))->interest_rate     ?? 8;
    $fixed3Min       = optional($ss->get('fixed_3'))->min_amount        ?? 500;
    

    $fixed6Rate      = optional($ss->get('fixed_6'))->interest_rate     ?? 10;
    $fixed6Min       = optional($ss->get('fixed_6'))->min_amount        ?? 500;
    $fixed12Rate     = optional($ss->get('fixed_12'))->interest_rate    ?? 14;
    $fixed12Min      = optional($ss->get('fixed_12'))->min_amount       ?? 500;
    $farmRate        = optional($ss->get('farm'))->interest_rate        ?? 12;
    $farmMin         = optional($ss->get('farm'))->min_amount           ?? 1000;
@endphp
<div class="nc-wrap" id="ncWrap">
    <br/>
    {{-- ── Page Header ─────────────────────────────────── --}}
    <div class="sl-page-header d-flex flex-wrap align-items-center gap-3 mb-4">
        <a href="{{ route('user.savings.index') }}" class="sl-back-btn" aria-label="Back to savings">
            <i class="las la-arrow-left"></i>
        </a>
        <div>
            <h4 class="sl-page-title mb-0">Create Savings Product</h4>
            <p class="sl-page-subtitle mb-0">Choose a product type and configure your savings goal.</p>
        </div>
    </div>

    {{-- ── Insufficient balance alert ───────────────────── --}}
    @if($savingsBalance <= 0)
    <div class="alert sl-alert-danger d-flex align-items-start gap-3 mb-4" role="alert">
        <i class="las la-exclamation-triangle fs-5 mt-1 flex-shrink-0"></i>
        <div>
            <strong>Insufficient Money Box balance.</strong>
            Your Money Box (available balance) is <strong>{{ showAmount($savingsBalance) }}</strong>.
            Deposit funds to your account first before creating a savings product.
            <a href="{{ route('user.deposit.index') }}" class="alert-link ms-1">Deposit →</a>
        </div>
    </div>
    @endif

    <div class="row g-4">

        {{-- ── LEFT: Step form ─────────────────────────── --}}
        <div class="col-12 col-lg-7">

            {{-- Step 1: Type picker --}}
            <div class="sl-card mb-4">
                <div class="sl-card-header">
                    <span class="sl-step-num">1</span> Choose Product Type
                </div>
                <div class="sl-card-body">
                    <div class="row g-2" id="typePicker">
                        <div class="col-12 col-sm-4">
                            <label class="sl-type-option {{ $selectedType === 'target' ? 'active' : '' }} {{ !$targetEnabled ? 'sl-type-disabled' : '' }}" for="typeTarget">
                                <input type="radio" id="typeTarget" name="savings_type" value="target"
                                    {{ $selectedType === 'target' ? 'checked' : '' }}
                                    {{ !$targetEnabled ? 'disabled' : '' }}>
                                <div class="sl-type-option-icon sl-icon-blue">
                                    <i class="las la-bullseye"></i>
                                </div>
                                <span class="sl-type-option-name">Target</span>
                                <span class="sl-type-option-sub">Goal-based</span>
                                @if(!$targetEnabled)<span class="sl-unavailable-badge">Unavailable</span>@endif
                            </label>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="sl-type-option {{ $selectedType === 'fixed' ? 'active' : '' }} {{ !$fixedEnabled ? 'sl-type-disabled' : '' }}" for="typeFixed">
                                <input type="radio" id="typeFixed" name="savings_type" value="fixed"
                                    {{ $selectedType === 'fixed' ? 'checked' : '' }}
                                    {{ !$fixedEnabled ? 'disabled' : '' }}>
                                <div class="sl-type-option-icon sl-icon-gold">
                                    <i class="las la-box"></i>
                                </div>
                                <span class="sl-type-option-name">Fixed Box</span>
                                <span class="sl-type-option-sub">Lump-sum lock</span>
                                @if(!$fixedEnabled)<span class="sl-unavailable-badge">Unavailable</span>@endif
                            </label>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="sl-type-option {{ $selectedType === 'farm' ? 'active' : '' }} {{ !$farmEnabled ? 'sl-type-disabled' : '' }}" for="typeFarm">
                                <input type="radio" id="typeFarm" name="savings_type" value="farm"
                                    {{ $selectedType === 'farm' ? 'checked' : '' }}
                                    {{ !$farmEnabled ? 'disabled' : '' }}>
                                <div class="sl-type-option-icon sl-icon-green">
                                    <i class="las la-seedling"></i>
                                </div>
                                <span class="sl-type-option-name">Farm Yield</span>
                                <span class="sl-type-option-sub">Cycle-linked</span>
                                @if(!$farmEnabled)<span class="sl-unavailable-badge">Unavailable</span>@endif
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Target Savings Form --}}
            <div class="sl-card mb-4 sl-form-panel" id="formTarget" style="{{ $selectedType !== 'target' ? 'display:none' : '' }}">
                <div class="sl-card-header">
                    <span class="sl-step-num">2</span> Target Savings Details
                </div>
                <div class="sl-card-body">
                    @if(!$targetEnabled)
                        <div class="sl-unavailable-notice">
                            <i class="las la-ban me-2"></i>
                            Target Savings is currently unavailable for new subscriptions.
                        </div>
                    @else
                    <form action="{{ route('user.savings.store') }}" method="POST" id="targetForm" class="disableSubmission">
                        @csrf
                        <input type="hidden" name="type" value="target">

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="target_name">Savings Name <span class="sl-required">*</span></label>
                                <input type="text" class="sl-input @error('name') is-invalid @enderror"
                                    id="target_name" name="name" placeholder="e.g. Car, Rent, Education"
                                    value="{{ old('name') }}" required maxlength="60">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="target_amount">Target Amount <span class="sl-required">*</span></label>
                                <div class="sl-input-group">
                                    <span class="sl-input-prefix">{{ gs('cur_sym') }}</span>
                                    <input type="number" class="sl-input sl-input-has-prefix @error('target_amount') is-invalid @enderror"
                                        id="target_amount" name="target_amount" placeholder="0.00"
                                        value="{{ old('target_amount') }}" step="0.01" min="100" required>
                                </div>
                                @error('target_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="frequency">Contribution Frequency <span class="sl-required">*</span></label>
                                <select class="sl-select @error('frequency') is-invalid @enderror" id="frequency" name="frequency" required>
                                    <option value="">Choose frequency…</option>
                                    <option value="weekly"  {{ old('frequency') === 'weekly'  ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                                @error('frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="duration">Duration <span class="sl-required">*</span></label>
                                <select class="sl-select @error('duration') is-invalid @enderror" id="duration" name="duration" required>
                                    <option value="">Choose duration…</option>
                                    <option value="4"  {{ old('duration') == 4  ? 'selected' : '' }}>4 weeks (1 month)</option>
                                    <option value="12" {{ old('duration') == 12 ? 'selected' : '' }}>12 weeks (3 months)</option>
                                    <option value="24" {{ old('duration') == 24 ? 'selected' : '' }}>24 weeks (6 months)</option>
                                    <option value="3"  data-freq="monthly" {{ old('duration') == 3 ? 'selected' : '' }}>3 months</option>
                                    <option value="6"  data-freq="monthly" {{ old('duration') == 6 ? 'selected' : '' }}>6 months</option>
                                    <option value="12" data-freq="monthly" {{ old('duration') == 12 ? 'selected' : '' }}>12 months</option>
                                </select>
                                @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="start_date">Start Date <span class="sl-required">*</span></label>
                                <input type="date" class="sl-input @error('start_date') is-invalid @enderror"
                                    id="start_date" name="start_date"
                                    value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                    min="{{ now()->format('Y-m-d') }}" required>
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="end_date_display">End Date (auto-calculated)</label>
                                <input type="text" class="sl-input" id="end_date_display" readonly placeholder="Set frequency & duration">
                                <input type="hidden" id="end_date" name="end_date">
                            </div>
                            <div class="col-12">
                                <label class="sl-label" for="initial_funding">Amount to Lock (from Money Box) <span class="sl-required">*</span></label>
                                <div class="sl-input-group">
                                    <span class="sl-input-prefix">{{ gs('cur_sym') }}</span>
                                    <input type="number" class="sl-input sl-input-has-prefix @error('initial_funding') is-invalid @enderror"
                                        id="initial_funding" name="initial_funding" placeholder="0.00"
                                        value="{{ old('initial_funding') }}" step="0.01"
                                        min="1" max="{{ $savingsBalance }}" required>
                                </div>
                                <p class="sl-field-hint">Available: <strong>{{ showAmount($savingsBalance) }}</strong> in your Money Box</p>
                                @error('initial_funding')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="sl-form-actions mt-4">
                            <a href="{{ route('user.savings.index') }}" class="sl-btn sl-btn-outline">Cancel</a>
                            <button type="submit" class="sl-btn sl-btn-primary" {{ $savingsBalance <= 0 ? 'disabled' : '' }}>
                                <span class="sl-btn-text"><i class="las la-check me-1"></i>Create Savings</span>
                                <span class="sl-btn-loading d-none"><i class="las la-spinner la-spin me-1"></i>Creating…</span>
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Step 2: Fixed Box Form --}}
            <div class="sl-card mb-4 sl-form-panel" id="formFixed" style="{{ $selectedType !== 'fixed' ? 'display:none' : '' }}">
                <div class="sl-card-header">
                    <span class="sl-step-num">2</span> Fixed Box Details
                </div>
                <div class="sl-card-body">
                    @if(!$fixedEnabled)
                        <div class="sl-unavailable-notice">
                            <i class="las la-ban me-2"></i>
                            Fixed Box Savings is currently unavailable for new subscriptions.
                        </div>
                    @else
                    <form action="{{ route('user.savings.store') }}" method="POST" class="disableSubmission">
                        @csrf
                        <input type="hidden" name="type" value="fixed">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="fixed_amount">Amount to Lock <span class="sl-required">*</span></label>
                                <div class="sl-input-group">
                                    <span class="sl-input-prefix">{{ gs('cur_sym') }}</span>
                                    <input type="number" class="sl-input sl-input-has-prefix @error('amount') is-invalid @enderror"
                                        id="fixed_amount" name="amount" placeholder="0.00"
                                        step="0.01" min="{{ $fixed3Min }}" max="{{ $savingsBalance }}" required>
                                </div>
                                <p class="sl-field-hint">Available: <strong>{{ showAmount($savingsBalance) }}</strong> in Money Box</p>
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="fixed_duration">Fixed Duration <span class="sl-required">*</span></label>
                                <select class="sl-select" id="fixed_duration" name="duration" required
                                        data-rates="{{ json_encode(['1'=>$fixed1Rate,'3'=>$fixed3Rate,'6'=>$fixed6Rate,'12'=>$fixed12Rate]) }}"
                                        data-mins="{{ json_encode(['1'=>$fixed1Min,'3'=>$fixed3Min,'6'=>$fixed6Min,'12'=>$fixed12Min]) }}">
                                    <option value="">Choose term…</option>
                                    @if($ss->has('fixed_1'))
                                    <option value="1">1 month — Est. {{ $fixed1Rate }}% p.a.</option>
                                    @endif

                                    @if($ss->has('fixed_3'))
                                    <option value="3">3 months — Est. {{ $fixed3Rate }}% p.a.</option>
                                    @endif
                                    @if($ss->has('fixed_6'))
                                    <option value="6">6 months — Est. {{ $fixed6Rate }}% p.a.</option>
                                    @endif
                                    @if($ss->has('fixed_12'))
                                    <option value="12">12 months — Est. {{ $fixed12Rate }}% p.a.</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="sl-info-banner">
                                    <i class="las la-info-circle me-2"></i>
                                    Fixed Box funds are <strong>locked until maturity</strong>. No early withdrawal is allowed.
                                </div>
                            </div>
                        </div>
                        <div class="sl-form-actions mt-4">
                            <a href="{{ route('user.savings.index') }}" class="sl-btn sl-btn-outline">Cancel</a>
                            <button type="submit" class="sl-btn sl-btn-primary" {{ $savingsBalance <= 0 ? 'disabled' : '' }}>
                                <i class="las la-lock me-1"></i>Lock Funds
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Step 2: Farm Yield Form --}}
            <div class="sl-card mb-4 sl-form-panel" id="formFarm" style="{{ $selectedType !== 'farm' ? 'display:none' : '' }}">
                <div class="sl-card-header">
                    <span class="sl-step-num">2</span> Farm Yield Savings Details
                </div>
                <div class="sl-card-body">
                    @if(!$farmEnabled)
                        <div class="sl-unavailable-notice">
                            <i class="las la-ban me-2"></i>
                            Farm Yield Savings is currently unavailable for new subscriptions.
                        </div>
                    @else
                    <form action="{{ route('user.savings.store') }}" method="POST" class="disableSubmission">
                        @csrf
                        <input type="hidden" name="type" value="farm">

                        {{-- Active cycles --}}
                        <div class="mb-4">
                            <label class="sl-label mb-2">Select Farm Cycle <span class="sl-required">*</span></label>
                            @if($farmCycles->isEmpty())
                                <div class="sl-empty-state py-3">
                                    <div class="sl-empty-icon" style="font-size:2rem"><i class="las la-seedling"></i></div>
                                    <p class="sl-empty-title mb-1" style="font-size:.9rem">No open farm cycles</p>
                                    <p class="sl-empty-sub">Check back later when new cycles are announced.</p>
                                </div>
                            @else
                                <div class="row g-2">
                                    @foreach($farmCycles as $cycle)
                                    <div class="col-12">
                                        <label class="sl-cycle-option" for="cycle_{{ $cycle->id }}">
                                            <input type="radio" name="farm_cycle_id" id="cycle_{{ $cycle->id }}" value="{{ $cycle->id }}"
                                                data-min_yield="{{ $cycle->min_yield }}"
                                                data-max_yield="{{ $cycle->max_yield }}"
                                                data-maturity="{{ $cycle->maturity_date?->format('M d, Y') ?? '—' }}"
                                                required>
                                            <div class="sl-cycle-info">
                                                <p class="fw-600 mb-0">{{ $cycle->name }}</p>
                                                <small class="text-muted">Closes {{ $cycle->subscription_closes_at?->format('M d, Y') ?? '—' }}</small>
                                            </div>
                                            <div class="sl-cycle-stats">
                                                <span class="sl-type-badge sl-type-farm">{{ $cycle->status }}</span>
                                                <small class="text-muted d-block mt-1">Yield: {{ $cycle->min_yield }}–{{ $cycle->max_yield }}%</small>
                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label class="sl-label" for="farm_amount">Contribution Amount <span class="sl-required">*</span></label>
                                <div class="sl-input-group">
                                    <span class="sl-input-prefix">{{ gs('cur_sym') }}</span>
                                    <input type="number" class="sl-input sl-input-has-prefix"
                                        id="farm_amount" name="amount" placeholder="0.00"
                                        step="0.01" min="{{ $farmMin }}" max="{{ $savingsBalance }}" required>
                                </div>
                                <p class="sl-field-hint">Min: {{ showAmount($farmMin) }} · Available: {{ showAmount($savingsBalance) }} in Money Box</p>
                            </div>
                        </div>

                        <div class="sl-form-actions mt-4">
                            <a href="{{ route('user.savings.index') }}" class="sl-btn sl-btn-outline">Cancel</a>
                            <button type="submit" class="sl-btn sl-btn-primary" {{ ($savingsBalance <= 0 || $farmCycles->isEmpty()) ? 'disabled' : '' }}>
                                <i class="las la-seedling me-1"></i>Join Cycle
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

        </div>{{-- /col-lg-7 --}}

        {{-- ── RIGHT: Live preview panel ──────────────────── --}}
        <div class="col-12 col-lg-5">
            <div class="sl-preview-card sticky-lg-top" style="top:100px;">
                <div class="sl-preview-header">
                    <i class="las la-eye me-2"></i>Live Preview
                </div>

                <div class="sl-preview-body" id="previewPanel">
                    {{-- Populated by JS --}}
                    <div class="sl-preview-placeholder" id="previewPlaceholder">
                        <i class="las la-chart-line"></i>
                        <p>Fill in the form details to see a<br>real-time savings projection.</p>
                    </div>

                    <div id="previewContent" style="display:none;">
                        <div class="sl-preview-row">
                            <span>Product Type</span>
                            <strong id="prv_type">—</strong>
                        </div>
                        <div class="sl-preview-row">
                            <span>Savings Goal</span>
                            <strong id="prv_goal">—</strong>
                        </div>
                        <div class="sl-preview-row">
                            <span>Frequency</span>
                            <strong id="prv_freq">—</strong>
                        </div>
                        <div class="sl-preview-row">
                            <span>Per Cycle Contribution</span>
                            <strong id="prv_contribution">—</strong>
                        </div>
                        <div class="sl-preview-divider"></div>
                        <div class="sl-preview-row">
                            <span>Maturity Date</span>
                            <strong id="prv_maturity">—</strong>
                        </div>
                        <div class="sl-preview-row">
                            <span>Principal</span>
                            <strong id="prv_principal">—</strong>
                        </div>
                        <div class="sl-preview-row sl-preview-highlight">
                            <span>Projected Interest</span>
                            <strong id="prv_interest" class="text-success">—</strong>
                        </div>
                        <div class="sl-preview-row sl-preview-total">
                            <span>Total at Maturity</span>
                            <strong id="prv_total">—</strong>
                        </div>
                        <div class="sl-preview-progress-wrap mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Goal Progress</small>
                                <small id="prv_pct" class="fw-600 text-success">0%</small>
                            </div>
                            <div class="progress sl-progress">
                                <div class="progress-bar sl-progress-bar" id="prv_bar" style="width:0%"></div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Wallet info --}}
                <div class="sl-preview-footer">
                    <div class="sl-preview-wallet">
                        <div>
                            <p class="mb-0 small text-muted">Money Box</p>
                            <p class="mb-0 fw-700 fs-6">{{ showAmount($savingsBalance) }}</p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 small text-muted">Remaining after lock</p>
                            <p class="mb-0 fw-700 fs-6 text-danger" id="prv_remaining">{{ showAmount($savingsBalance) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</div>{{-- /nc-wrap --}}
@endsection

@push('style')
<style>
/* ── Reuse savings index styles ── */
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
.sl-btn-primary:hover:not([disabled]) { background:#094422; color:#fff; transform:translateY(-1px); box-shadow:0 4px 14px rgba(13,92,46,.25); }
.sl-btn-outline { background:#fff; color:var(--sl-green); border:1.5px solid var(--sl-green); }
.sl-btn-outline:hover { background:var(--sl-green-lt); }
.sl-btn[disabled] { opacity:.45; cursor:not-allowed; }

.sl-alert-danger { background:#FFF1F2; border:1px solid #FECDD3; border-radius:12px; color:#9F1239; padding:1rem 1.25rem; }

.sl-card { background:#fff; border-radius:var(--sl-radius); border:1px solid var(--sl-border); box-shadow:var(--sl-shadow); overflow:hidden; }
.sl-card-header { padding:1rem 1.25rem; border-bottom:1px solid var(--sl-border); font-size:.95rem; font-weight:700; color:var(--bk-text); display:flex; align-items:center; gap:.6rem; }
.sl-card-body { padding:1.5rem; }

.sl-step-num { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:var(--sl-green); color:#fff; font-size:.7rem; font-weight:800; flex-shrink:0; }

/* Type picker */
.sl-type-option {
    display:flex; flex-direction:column; align-items:center; gap:.4rem; text-align:center;
    padding:1rem .75rem; border-radius:12px; border:2px solid var(--sl-border);
    cursor:pointer; background:#FAFAFA; transition:all .2s; position:relative; width:100%;
}
.sl-type-option input[type=radio] { position:absolute; opacity:0; }
.sl-type-option:hover { border-color:var(--sl-green); background:var(--sl-green-lt); }
.sl-type-option.active, .sl-type-option:has(input:checked) { border-color:var(--sl-green); background:var(--sl-green-lt); }
.sl-type-option-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; }
.sl-icon-blue  { background:var(--sl-blue-lt); color:var(--sl-blue); }
.sl-icon-gold  { background:var(--sl-gold-lt); color:var(--sl-gold); }
.sl-icon-green { background:var(--sl-green-lt); color:var(--sl-green); }
.sl-type-option-name { font-size:.88rem; font-weight:700; color:var(--bk-text); }
.sl-type-option-sub  { font-size:.72rem; color:var(--bk-muted); }

/* Form controls */
.sl-label { display:block; font-size:.82rem; font-weight:600; color:var(--bk-text); margin-bottom:.45rem; }
.sl-required { color:#DC2626; }
.sl-input, .sl-select {
    display:block; width:100%; padding:.6rem .85rem; font-size:.875rem;
    border:1.5px solid var(--sl-border); border-radius:9px; background:#fff;
    color:var(--bk-text); transition:border-color .2s, box-shadow .2s;
    -webkit-appearance:none; appearance:none;
}
.sl-select { background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"); background-repeat:no-repeat; background-position:right .75rem center; background-size:14px; padding-right:2.5rem; }
.sl-input:focus, .sl-select:focus { outline:none; border-color:var(--sl-green); box-shadow:0 0 0 3px rgba(13,92,46,.12); }
.sl-input-group { position:relative; }
.sl-input-prefix { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:var(--bk-muted); font-size:.875rem; font-weight:600; pointer-events:none; z-index:1; }
.sl-input-has-prefix { padding-left:2.2rem; }
.sl-field-hint { font-size:.75rem; color:var(--bk-muted); margin-top:.35rem; margin-bottom:0; }
.sl-info-banner { background:var(--sl-blue-lt); border:1px solid #BFDBFE; border-radius:9px; padding:.75rem 1rem; font-size:.84rem; color:var(--sl-blue); }
.sl-form-actions { display:flex; gap:.75rem; flex-wrap:wrap; }

/* Cycle picker */
.sl-cycle-option {
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
    padding:.85rem 1rem; border-radius:10px; border:2px solid var(--sl-border);
    cursor:pointer; background:#FAFAFA; transition:all .2s; position:relative; width:100%;
}
.sl-cycle-option input[type=radio] { position:absolute; opacity:0; }
.sl-cycle-option:hover, .sl-cycle-option:has(input:checked) { border-color:var(--sl-green); background:var(--sl-green-lt); }
.sl-cycle-info { flex:1; }
.sl-cycle-stats { text-align:right; }

/* Preview card */
.sl-preview-card { background:#fff; border-radius:var(--sl-radius); border:1px solid var(--sl-border); box-shadow:var(--sl-shadow); overflow:hidden; }
.sl-preview-header { padding:.9rem 1.25rem; background:linear-gradient(135deg, #0D5C2E, #16A34A); color:#fff; font-size:.88rem; font-weight:700; display:flex; align-items:center; }
.sl-preview-body { padding:1.25rem; min-height:200px; }
.sl-preview-placeholder { text-align:center; padding:2rem 1rem; color:#9CA3AF; }
.sl-preview-placeholder i { font-size:2.5rem; display:block; margin-bottom:.75rem; }
.sl-preview-placeholder p { font-size:.84rem; }
.sl-preview-row { display:flex; justify-content:space-between; align-items:center; padding:.5rem 0; border-bottom:1px solid #F3F4F6; font-size:.86rem; color:var(--bk-muted); }
.sl-preview-row strong { color:var(--bk-text); }
.sl-preview-row:last-child { border-bottom:0; }
.sl-preview-divider { height:0; border-top:2px dashed #E5E9EF; margin:.5rem 0; }
.sl-preview-highlight { background:#F0FDF4; margin:0 -1.25rem; padding:.5rem 1.25rem; }
.sl-preview-total { background:var(--sl-green-lt); margin:0 -1.25rem; padding:.65rem 1.25rem; font-weight:700; }
.sl-preview-total strong { color:var(--sl-green); font-size:1rem; }
.sl-preview-footer { padding:1rem 1.25rem; border-top:1px solid var(--sl-border); background:#FAFAFA; }
.sl-preview-wallet { display:flex; justify-content:space-between; align-items:center; }
.sl-preview-progress-wrap { padding-top:.5rem; }
.sl-progress { height:7px; border-radius:999px; background:#E5E9EF; }
.sl-progress-bar { background:linear-gradient(90deg, #16A34A, #0D5C2E); border-radius:999px; transition:width .4s ease; }

.sl-type-badge { font-size:.72rem; font-weight:700; padding:.25rem .55rem; border-radius:6px; text-transform:capitalize; }
.sl-type-farm  { background:var(--sl-green-lt); color:var(--sl-green); }

.sl-type-disabled { opacity:.55; cursor:not-allowed; pointer-events:none; }
.sl-unavailable-badge { font-size:.7rem; font-weight:700; background:#FEE2E2; color:#DC2626; padding:.2rem .5rem; border-radius:6px; }
.sl-unavailable-notice { background:#FFF1F2; border:1px solid #FECDD3; border-radius:10px; padding:1.25rem 1.5rem; color:#9F1239; font-size:.875rem; }

.fw-600 { font-weight:600; }
.fw-700 { font-weight:700; }
.fs-6 { font-size:1rem !important; }
</style>
@endpush

@push('script')
<script>
$(function () {
    'use strict';

    var RATE    = {{ $targetRate }} / 100;
    var CUR     = '{{ gs("cur_sym") }}';
    var BALANCE = {{ $savingsBalance }};

    // Use form-scoped selector — global layout rewrites id to match name,
    // so id="fixed_duration" (name="duration") becomes id="duration" and is unreachable by old selector.
    var FIXED_RATES = (function () {
        var raw = $('#formFixed [name="duration"]').data('rates') || {};
        var out = {};
        $.each(raw, function (k, v) { out[k] = parseFloat(v) / 100; });
        return out;
    }());
    var FIXED_MINS = $('#formFixed [name="duration"]').data('mins') || {};

    function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
    function fmt(n) { return Number(n).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    /* ── Type picker ─────────────────────────────── */
    $('input[name="savings_type"]').on('change', function () {
        $('.sl-type-option').removeClass('active');
        $(this).closest('.sl-type-option').addClass('active');
        $('.sl-form-panel').hide();
        $('#form' + capitalize($(this).val())).show();
        resetPreview();
    });

    /* ── Target form → live preview ─────────────── */
    $('#targetForm').on('input change', '[name="target_amount"],[name="frequency"],[name="duration"],[name="start_date"],[name="initial_funding"]', recalcTarget);

    function recalcTarget() {
        var $f       = $('#targetForm');
        var goal     = parseFloat($f.find('[name="target_amount"]').val()) || 0;
        var freq     = $f.find('[name="frequency"]').val();
        var dur      = parseInt($f.find('[name="duration"]').val()) || 0;
        var startVal = $f.find('[name="start_date"]').val();
        var init     = parseFloat($f.find('[name="initial_funding"]').val()) || 0;

        if (!goal || !freq || !dur || !startVal) { resetPreview(); return; }

        var contribution = goal / dur;
        var weeks        = freq === 'weekly' ? dur : dur * 4;
        var years        = weeks / 52;
        var interest     = goal * RATE * years;
        var total        = goal + interest;
        var pct          = Math.min(100, (init / goal) * 100);

        var start = new Date(startVal);
        var end   = new Date(start);
        if (freq === 'weekly') end.setDate(end.getDate() + dur * 7);
        else                   end.setMonth(end.getMonth() + dur);

        var endFormatted = end.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        $('#end_date_display').val(endFormatted);
        $('#end_date').val(end.toISOString().slice(0, 10));

        var remaining = BALANCE - init;
        $('#prv_remaining').text(CUR + fmt(remaining))
                           .removeClass('text-danger text-success')
                           .addClass(remaining < 0 ? 'text-danger' : 'text-success');

        showPreview({
            type:         'Target Savings',
            goal:         CUR + fmt(goal),
            freq:         capitalize(freq),
            contribution: CUR + fmt(contribution) + ' / ' + (freq === 'weekly' ? 'week' : 'month'),
            maturity:     endFormatted,
            principal:    CUR + fmt(goal),
            interest:     '+' + CUR + fmt(interest),
            total:        CUR + fmt(total),
            pct:          Math.round(pct)
        });
    }

    /* ── Fixed Box → live preview ───────────────── */
    $('#formFixed').on('input change', '[name="amount"],[name="duration"]', recalcFixed);

    function recalcFixed() {
        var $f     = $('#formFixed');
        var amount = parseFloat($f.find('[name="amount"]').val()) || 0;
        var dur    = $f.find('[name="duration"]').val();
        if (dur) {
            var minAmt = parseFloat(FIXED_MINS[dur]) || 0;
            $f.find('[name="amount"]').attr('min', minAmt);
        }
        if (!amount || !dur) { resetPreview(); return; }

        var rate      = FIXED_RATES[dur] || 0.08;
        var years     = parseInt(dur) / 12;
        var interest  = amount * rate * years;
        var total     = amount + interest;
        var end       = new Date();
        end.setMonth(end.getMonth() + parseInt(dur));

        var remaining = BALANCE - amount;
        $('#prv_remaining').text(CUR + fmt(remaining))
                           .removeClass('text-danger text-success')
                           .addClass(remaining < 0 ? 'text-danger' : 'text-success');

        showPreview({
            type:         'Fixed Box',
            goal:         CUR + fmt(amount),
            freq:         dur + ' months fixed',
            contribution: '—',
            maturity:     end.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }),
            principal:    CUR + fmt(amount),
            interest:     '+' + CUR + fmt(interest) + ' (' + (rate * 100).toFixed(0) + '% p.a.)',
            total:        CUR + fmt(total),
            pct:          100
        });
    }

    /* ── Farm Yield → live preview ──────────────── */
    $('#formFarm').on('input change', '[name="amount"],[name="farm_cycle_id"]', recalcFarm);

    function recalcFarm() {
        var amount = parseFloat($('#formFarm [name="amount"]').val()) || 0;
        var $radio = $('input[name="farm_cycle_id"]:checked');

        if (!amount || !$radio.length) { resetPreview(); return; }

        var minYield = parseFloat($radio.data('min_yield')) || 0;
        var maxYield = parseFloat($radio.data('max_yield')) || 0;
        var midRate  = (minYield + maxYield) / 2 / 100;
        var maturity = $radio.data('maturity') || '—';
        var interest = amount * midRate;
        var total    = amount + interest;

        var remaining = BALANCE - amount;
        $('#prv_remaining').text(CUR + fmt(remaining))
                           .removeClass('text-danger text-success')
                           .addClass(remaining < 0 ? 'text-danger' : 'text-success');

        showPreview({
            type:         'Farm Yield',
            goal:         CUR + fmt(amount),
            freq:         'Cycle-linked',
            contribution: '—',
            maturity:     maturity,
            principal:    CUR + fmt(amount),
            interest:     '+' + CUR + fmt(interest) + ' (~' + ((minYield + maxYield) / 2).toFixed(1) + '% est.)',
            total:        CUR + fmt(total),
            pct:          100
        });
    }

    /* ── Helpers ─────────────────────────────────── */
    function showPreview(d) {
        $('#previewPlaceholder').hide();
        $('#previewContent').show();
        $('#prv_type').text(d.type);
        $('#prv_goal').text(d.goal);
        $('#prv_freq').text(d.freq);
        $('#prv_contribution').text(d.contribution);
        $('#prv_maturity').text(d.maturity);
        $('#prv_principal').text(d.principal);
        $('#prv_interest').text(d.interest);
        $('#prv_total').text(d.total);
        $('#prv_pct').text(d.pct + '%');
        $('#prv_bar').css('width', d.pct + '%');
    }

    function resetPreview() {
        $('#previewPlaceholder').show();
        $('#previewContent').hide();
        $('#prv_remaining').text(CUR + fmt(BALANCE));
    }

    /* ── Loading state on submit ─────────────────── */
    $('form').on('submit', function () {
        var $btn     = $(this).find('.sl-btn-primary');
        var $btnText = $(this).find('.sl-btn-text');
        var $btnLoad = $(this).find('.sl-btn-loading');
        if ($btn.length && $btnText.length && $btnLoad.length) {
            $btn.prop('disabled', true);
            $btnText.addClass('d-none');
            $btnLoad.removeClass('d-none');
        }
    });
});
</script>
@endpush
