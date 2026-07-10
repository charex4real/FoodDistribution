@extends($activeTemplate.'layouts.master2')
@section('content')
<div class="container padding-bottom padding-top">
        <div class="row justify-content-center">
            <div class="col-md-8 col-xl-8 bg-opacity-75 "  style="background-color:rgb(184 239 196 / 85%)">
                
                <div class="min-vh-100 py-3  text-dark">
                    <div class="container" style="max-width: 600px;">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                             
                            </div>
                            <h2 class="h4 fw-semibold mb-2 text-dark">Complete Your Deposit</h2>
                            
                        </div>

                        <!-- Member Information Card -->
                       

                        <!-- Payment Summary Card -->
                        <div class="card mb-4">
                            <div class="card-header d-flex align-items-center gap-2">
                                <i class="bi bi-credit-card"></i>
                                <h5 class="card-title mb-0">Payment Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">  @lang('You have to pay '):</span>
                                    <span class="fw-semibold">{{showAmount($deposit->final_amount,currencyFormat:false)}} {{__($deposit->method_currency)}}</span>
                                </div>
                            </div>
                        </div>

                        

                        <!-- Payment Action -->
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <span class="badge bg-success bg-opacity-25 text-success">Secure Payment Processing</span>
                                </div>
                                
                                
                                <form action="{{ route('ipn.'.$deposit->gateway->alias) }}" method="POST" class="text-center">
                                    @csrf

                                    <button type="button" class="btn btn-success w-100 mt-3" id="btn-confirm">@lang('Pay Now')</button>
                                    <script
                                        src="//js.paystack.co/v1/inline.js"
                                        data-key="{{ $data->key }}"
                                        data-email="{{ $data->email }}"
                                        data-amount="{{ round($data->amount) }}"
                                        data-currency="{{$data->currency}}"
                                        data-ref="{{ $data->ref }}"
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
@endsection
