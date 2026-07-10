@extends($activeTemplate . 'layouts.master2')
@section('content')
    <div class="text-end mb-3">
        <a class="btn btn--base" href="{{ route('user.deposit.history1') }}">
          <i class="las la-list"></i> @lang('Deposit History')
        </a>
    </div>
    <div class="card custom--card">
        <div class="card-body">
            <form action="{{ route('user.deposit.insert1') }}" method="post" class="deposit-form">
                @csrf
                <input type="hidden" name="currency">
                <div class="gateway-card">
                    <div class="row justify-content-center gy-sm-4 gy-3">
                        <div class="col-lg-6">
                            {{-- View Account Detail Button --}}
                            <button type="button" class="dep-acct-btn mb-3" onclick="openBankModal()">
                                <span class="dep-acct-btn-inner">
                                    <i class="las la-university"></i>
                                    <span>@lang('Click to View Account Details')</span>
                                </span>
                                <i class="las la-chevron-right dep-acct-btn-arrow"></i>
                            </button>

                            <div class="payment-system-list is-scrollable gateway-option-list">
                                @foreach ($gatewayCurrency as $data)
                                    @if ($data->method_code == 107)
                                    <label for="{{ titleToKey($data->name) }}"
                                        class="payment-item @if ($loop->index > 4) d-none @endif gateway-option">
                                        <div class="payment-item__info">
                                            <span class="payment-item__check"></span>
                                            <span class="payment-item__name">{{ __($data->name) }}</span>
                                        </div>
                                        <div class="payment-item__thumb">
                                            <img class="payment-item__thumb-img" src="{{ getImage(getFilePath('gateway') . '/' . $data->method->image) }}"
                                                alt="@lang('payment-thumb')">
                                        </div>
                                        <input class="payment-item__radio gateway-input" id="{{ titleToKey($data->name) }}" hidden
                                            data-gateway='@json($data)' type="radio" name="gateway" value="{{ $data->method_code }}"
                                            @if (old('gateway')) @checked(old('gateway') == $data->method_code) @else @checked($loop->first) @endif
                                            data-min-amount="{{ showAmount($data->min_amount) }}" data-max-amount="{{ showAmount($data->max_amount) }}">
                                    </label>
                                    @endif
                                @endforeach
                                @if ($gatewayCurrency->count() > 4)
                                    <button type="button" class="payment-item__btn more-gateway-option mt-3">
                                        <p class="payment-item__btn-text">@lang('Show All Payment Options')</p>
                                        <span class="payment-item__btn__icon"><i class="fas fa-chevron-down"></i></span>
                                    </button>
                                @endif

                            </div>
                        </div>

{{-- Company Bank Details Modal --}}
<div id="bankDetailModal" class="bdm-overlay" onclick="if(event.target===this)closeBankModal()">
    <div class="bdm-box">
        <div class="bdm-header">
            <div class="bdm-header-icon"><i class="las la-university"></i></div>
            <div>
                <h5 class="bdm-title">@lang('Company Bank Details')</h5>
                <p class="bdm-subtitle">@lang('Transfer funds to the account below')</p>
            </div>
            <button type="button" class="bdm-close" onclick="closeBankModal()"><i class="las la-times"></i></button>
        </div>
        <div class="bdm-body">
            <div class="bdm-field">
                <span class="bdm-field-label"><i class="las la-landmark"></i> @lang('Bank Name')</span>
                <div class="bdm-field-val-wrap">
                    <span class="bdm-field-val" id="bdm-bank-name">First Bank of Nigeria</span>
                    <button type="button" class="bdm-copy-btn" onclick="bdmCopy('bdm-bank-name', this)"><i class="las la-copy"></i></button>
                </div>
            </div>
            <div class="bdm-field">
                <span class="bdm-field-label"><i class="las la-user-circle"></i> @lang('Account Name')</span>
                <div class="bdm-field-val-wrap">
                    <span class="bdm-field-val" id="bdm-acct-name">XYZ Company Limited</span>
                    <button type="button" class="bdm-copy-btn" onclick="bdmCopy('bdm-acct-name', this)"><i class="las la-copy"></i></button>
                </div>
            </div>
            <div class="bdm-field">
                <span class="bdm-field-label"><i class="las la-hashtag"></i> @lang('Account Number')</span>
                <div class="bdm-field-val-wrap">
                    <span class="bdm-field-val bdm-acno" id="bdm-acct-no">1234567890</span>
                    <button type="button" class="bdm-copy-btn" onclick="bdmCopy('bdm-acct-no', this)"><i class="las la-copy"></i></button>
                </div>
            </div>
        </div>
        <div class="bdm-note">
            <i class="las la-info-circle"></i>
            @lang('After transfer, submit your deposit and upload proof of payment if required.')
        </div>
        <button type="button" class="bdm-done-btn" onclick="closeBankModal()">
            <i class="las la-check-circle"></i> @lang('Got it')
        </button>
    </div>
</div>
                        <div class="col-lg-6">
                            <div class="payment-system-list p-3">
                                <div class="deposit-info">
                                    <div class="deposit-info__title">
                                        <p class="text mb-0">@lang('Amount')</p>
                                    </div>
                                    <div class="deposit-info__input">
                                        <div class="deposit-info__input-group input-group">
                                            <span class="deposit-info__input-group-text px-2">{{ gs('cur_sym') }}</span>
                                            <input type="text" class="form-control form--control amount" name="amount" placeholder="@lang('00.00')"
                                                value="{{ old('amount') }}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="deposit-info">
                                    <div class="deposit-info__title">
                                        <p class="text has-icon"> @lang('Limit')
                                            <span></span>
                                        </p>
                                    </div>
                                    <div class="deposit-info__input">
                                        <p class="text"><span class="gateway-limit">@lang('0.00')</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="deposit-info">
                                    <div class="deposit-info__title">
                                        <p class="text has-icon">@lang('Processing Charge')
                                            <span data-bs-toggle="tooltip" title="@lang('Processing charge for payment gateways')" class="proccessing-fee-info"><i
                                                    class="las la-info-circle"></i> </span>
                                        </p>
                                    </div>
                                    <div class="deposit-info__input">
                                        <p class="text"><span class="processing-fee">@lang('0.00')</span>
                                            {{ __(gs('cur_text')) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="deposit-info total-amount pt-3">
                                    <div class="deposit-info__title">
                                        <p class="text">@lang('Total')</p>
                                    </div>
                                    <div class="deposit-info__input">
                                        <p class="text"><span class="final-amount">@lang('0.00')</span>
                                            {{ __(gs('cur_text')) }}</p>
                                    </div>
                                </div>

                                <div class="deposit-info gateway-conversion d-none total-amount pt-2">
                                    <div class="deposit-info__title">
                                        <p class="text">@lang('Conversion')
                                        </p>
                                    </div>
                                    <div class="deposit-info__input">
                                        <p class="text"></p>
                                    </div>
                                </div>
                                <div class="deposit-info conversion-currency d-none total-amount pt-2">
                                    <div class="deposit-info__title">
                                        <p class="text">
                                            @lang('In') <span class="gateway-currency"></span>
                                        </p>
                                    </div>
                                    <div class="deposit-info__input">
                                        <p class="text">
                                            <span class="in-currency"></span>
                                        </p>

                                    </div>
                                </div>
                                <div class="d-none crypto-message mb-3">
                                    @lang('Conversion with') <span class="gateway-currency"></span> @lang('and final value will Show on next step')
                                </div>
                                <div class="info-text pt-3">
                                    <p class="text"><span class="text-success"><strong class="text-dark">NOTE: </strong><strong>@lang('Upon payment confirmation money box will be credited.')</strong></span></p>
                                </div>
                                <br/>
                                <button type="submit" class="btn btn--base w-100" disabled>
                                    @lang('Confirm Deposit')
                                </button>
                               
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        function openBankModal() {
            document.getElementById('bankDetailModal').classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeBankModal() {
            document.getElementById('bankDetailModal').classList.remove('open');
            document.body.style.overflow = '';
        }
        function bdmCopy(id, btn) {
            var text = document.getElementById(id).innerText;
            navigator.clipboard.writeText(text).then(function() {
                btn.classList.add('copied');
                btn.innerHTML = '<i class="las la-check"></i>';
                setTimeout(function() { btn.classList.remove('copied'); btn.innerHTML = '<i class="las la-copy"></i>'; }, 1800);
            });
        }

        (function($) {

            var amount = parseFloat($('.amount').val() || 0);
            var gateway, minAmount, maxAmount;


            $('.amount').on('input', function(e) {
                amount = parseFloat($(this).val());
                if (!amount) {
                    amount = 0;
                }
                calculation();
            });

            $('.gateway-input').on('change', function(e) {
                gatewayChange();
            });

            function gatewayChange() {
                let gatewayElement = $('.gateway-input:checked');
                let methodCode = gatewayElement.val();

                gateway = gatewayElement.data('gateway');
                minAmount = gatewayElement.data('min-amount');
                maxAmount = gatewayElement.data('max-amount');

                let processingFeeInfo =
                    `${parseFloat(gateway.percent_charge).toFixed(2)}% with ${parseFloat(gateway.fixed_charge).toFixed(2)} {{ __(gs('cur_text')) }} charge for payment gateway processing fees`
                $(".proccessing-fee-info").attr("data-bs-original-title", processingFeeInfo);
                calculation();
            }

            gatewayChange();

            $(".more-gateway-option").on("click", function(e) {
                let paymentList = $(".gateway-option-list");
                paymentList.find(".gateway-option").removeClass("d-none");
                $(this).addClass('d-none');
                paymentList.animate({
                    scrollTop: (paymentList.height() - 60)
                }, 'slow');
            });

            function calculation() {
                if (!gateway) return;
                $(".gateway-limit").text(minAmount + " - " + maxAmount);

                let percentCharge = 0;
                let fixedCharge = 0;
                let totalPercentCharge = 0;

                if (amount) {
                    percentCharge = parseFloat(gateway.percent_charge);
                    fixedCharge = parseFloat(gateway.fixed_charge);
                    totalPercentCharge = parseFloat(amount / 100 * percentCharge);
                }

                let totalCharge = parseFloat(totalPercentCharge + fixedCharge);
                let totalAmount = parseFloat((amount || 0) + totalPercentCharge + fixedCharge);

                $(".final-amount").text(totalAmount.toFixed(2));
                $(".processing-fee").text(totalCharge.toFixed(2));
                $("input[name=currency]").val(gateway.currency);
                $(".gateway-currency").text(gateway.currency);

                if (amount < Number(gateway.min_amount) || amount > Number(gateway.max_amount)) {
                    $(".deposit-form button[type=submit]").attr('disabled', true);
                } else {
                    $(".deposit-form button[type=submit]").removeAttr('disabled');
                }

                if (gateway.currency != "{{ gs('cur_text') }}" && gateway.method.crypto != 1) {
                    $('.deposit-form').addClass('adjust-height')

                    $(".gateway-conversion, .conversion-currency").removeClass('d-none');
                    $(".gateway-conversion").find('.deposit-info__input .text').html(
                        `1 {{ __(gs('cur_text')) }} = <span class="rate">${parseFloat(gateway.rate).toFixed(2)}</span>  <span class="method_currency">${gateway.currency}</span>`
                    );
                    $('.in-currency').text(parseFloat(totalAmount * gateway.rate).toFixed(gateway.method.crypto == 1 ? 8 : 2))
                } else {
                    $(".gateway-conversion, .conversion-currency").addClass('d-none');
                    $('.deposit-form').removeClass('adjust-height')
                }

                if (gateway.method.crypto == 1) {
                    $('.crypto-message').removeClass('d-none');
                } else {
                    $('.crypto-message').addClass('d-none');
                }
            }

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
            $('.gateway-input').change();
        })(jQuery);
    </script>
@endpush
