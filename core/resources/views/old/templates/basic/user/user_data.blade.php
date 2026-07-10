@extends($activeTemplate . 'layouts.frontend_new')
@section('content')
 @php
       $user = getUserDetails(auth()->id());
 @endphp 
<div class="background-container">
 <!-- signup_payment_bg.jpg -->
    <div class="container padding-bottom padding-top">
        <div class="row justify-content-center">
            <div class="col-md-8 col-xl-8 bg-opacity-75 " style="background-color:rgb(184 239 196 / 75%)">
                
                <div class="min-vh-100 py-5  text-dark">
                    <div class="container" style="max-width: 600px;">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                               <img src="{{ asset($activeTemplateTrue . 'images/logo/Wordmark.png') }}" style="width:100px !important">
                                
                            </div>
                            <h2 class="h4 fw-semibold mb-2 text-dark">Complete Your Membership</h2>
                            <p class="text-muted">Review your information and proceed to payment</p>
                        </div>

                        <!-- Member Information Card -->
                        <div class="card mb-4">
                            <div class="card-header d-flex align-items-center gap-2">
                                <i class="bi bi-person"></i>
                                <div>
                                    <h5 class="card-title mb-0">Member Information</h5>
                                    <p class="text-muted small mb-0">Please verify your registration details</p>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="bi bi-person text-muted"></i>
                                            <div>
                                                <p class="small text-muted mb-1">@lang('Full Name')</p>
                                                <p class="fw-semibold mb-0">{{ $user->fullname }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="bi bi-envelope text-muted"></i>
                                            <div>
                                                <p class="small text-muted mb-1">@lang('Email Address')</p>
                                                <p class="fw-semibold mb-0">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="bi bi-telephone text-muted"></i>
                                            <div>
                                                <p class="small text-muted mb-1">@lang('Phone Number')</p>
                                                <p class="fw-semibold mb-0">{{ $user->mobile }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="bi bi-geo-alt text-muted"></i>
                                            <div>
                                                <p class="small text-muted mb-1">Address</p>
                                                <p class="fw-semibold mb-0">{{ $user->address }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Summary Card -->
                        {{-- <div class="card mb-4">
                            <div class="card-header d-flex align-items-center gap-2">
                                <i class="bi bi-credit-card"></i>
                                <h5 class="card-title mb-0">Payment Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Membership Registration Fee</span>
                                    <span class="fw-semibold">{{showAmount($data['amount']/100)}}</span>
                                </div>
                            </div>
                        </div>

                        --}}

                        <!-- Payment Action -->
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <span class="badge bg-success bg-opacity-25 text-success">Secure Payment Processing</span>
                                </div>
                                <p class="text-muted small mb-4">
                                    NOTE: Pin can be gotten from Ambassadors or click the chat icon on the buttom right to get yours. Thanks
                                </p>
                                
                                
                                

                                <form method="POST" action="{{ route('ipn.pinPay') }}">
                                    @csrf
                                    
                                    <div class="row">
                                            <div class="col-md-12">
                                                <div class="form--group text-center">
                                                   
                                                    <input type="text" class="form-control form--control checkUser" name="pin" value="{{ old('pin') }}" required placeholder="PIN CODE">
                                                    <small class="text--danger usernameExist"></small>
                                                </div>
                                            </div>
                                    </div>
                                    <button type="submit" class="btn btn--base w-100">
                                        @lang('Submit')
                                    </button>
                                </form>
                                <p class="small text-muted mb-0">By Submiting, you agree to our  <a href="{{ route('agreement') }}" target="_blank">Terms of Use and Member Investment Policy</a></p>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
        .background-container {
        background-image: url("{{ asset($activeTemplateTrue . 'images/signup_payment_bg.jpg') }}");
        background-size: cover; /* Ensures the image covers the entire element */
        background-position: center center; /* Centers the image */
        background-repeat: no-repeat; /* Prevents image repetition */
      }
    </style>
@endpush

