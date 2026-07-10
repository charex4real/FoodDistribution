@extends($activeTemplate . 'layouts.frontend')
@section('content') 

    <div class="container padding-bottom padding-top">
        <div class="row justify-content-center">
            <div class="col-md-8 col-xl-6">
                <div class="card custom--card">
                    <div class="card-body">
                         <div class="col-md-12">
                            <div class="card custom--card">
                                <div class="card-header">
                                    <h5 class="card-title text-center">@lang('Paystack')</h5>
                                </div>
                                <div class="card-body p-5">
                                    <form action="{{ route('ipn.Paystack1') }}" method="POST" class="text-center">
                                        @csrf
                                        <ul class="list-group text-center">
                                            <li class="list-group-item d-flex justify-content-between">
                                                @lang('You have to pay '):
                                                <strong>{{showAmount($data['amount']/100, currencyFormat:false)}} {{__('NGN')}}</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                @lang('You will get '):
                                                <strong>{{showAmount($data['amount']/100)}}</strong>
                                            </li>
                                        </ul>
                                        <button type="button" class="btn btn--base w-100 mt-3" id="btn-confirm">@lang('Pay Now')</button>
                                        <script
                                            src="//js.paystack.co/v1/inline.js"
                                            data-key="{{ $data['key'] }}"
                                            data-email="{{ $data['email'] }}"
                                            data-amount="{{ round($data['amount']) }}"
                                            data-currency="{{$data['currency']}}"
                                            data-ref="{{ $data['ref'] }}"
                                            data-custom-button="btn-confirm"
                                        >
                                        </script>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

@push('style')
    <style>
        .form-disabled {
            overflow: hidden;
            position: relative;
        }

        .form-disabled::after {
            content: "";
            position: absolute;
            height: 100%;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.2);
            top: 0;
            left: 0;
            backdrop-filter: blur(2px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            z-index: 99;
        }

        .form-disabled-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 991;
            font-size: 24px;
            height: auto;
            width: 100%;
            text-align: center;
            color: hsl(var(--dark-600));
            font-weight: 800;
            line-height: 1.2;
        }
    </style>
@endpush

