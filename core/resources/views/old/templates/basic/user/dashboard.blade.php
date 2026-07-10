@extends($activeTemplate . 'layouts.master')
@section('content')
 @include($activeTemplate.'layouts.breadcrumb')
    <div class="container">
        <div class="row">
            

            <div class="row justify-content-center g-3">
                {{-- 
                @if(!checkIfUserIsInMatrix(auth()->id()))
                <div class="col-sm-6 col-md-6 col-xl-4 mb-30 mb-4">
                    <div class="card custom--card">
                        <div class="card-body">
                            <div class="pricing-table mb-4 text-center">
                                <h3 class="package-name text- mb-20"><strong>{{ __('Activate Account') }}</strong></h3>
                                <span class="price text--dark font-weight-bold d-block">{{ showAmount(7000) }}</span>
                                 <span class="price text--dark font-weight-bold d-block">Current Balance : {{ showAmount(auth()->user()->balance) }}</span>
                                <hr>
                                
                            </div>
                            <div class="text-center">
                                <a class="cmn--btn active __subscribe" data-id="12" href="#"><span>@lang('Activate')</span></a>
                                   
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('VISA')</h6>
                                    <h3 class="ammount theme-one">{{ showAmount(auth()->user()->transactions_commission()->sum('amount')) }}</h3>
                                </div>
                                <div class="icon"><i class="flaticon-money-bag"></i></div>
                            </div>
                            
                        </div> 
                </div>
                 <div class="col-sm-6 col-md-6 col-xl-4 mb-30 mb-4">
                    <div class="card custom--card">
                        <div class="card-body">
                            <div class="pricing-table mb-4 text-center">
                                <h3 class="package-name text- mb-20"><strong>{{ __('Deposite Money') }}</strong></h3>
                                
                                <hr>
                                
                            </div>
                            <div class="text-center">
                                <a  href="{{ route('user.deposit.index') }}"  class="cmn--btn active" ><span>@lang('Deposite Money')</span></a>
                                   
                            </div>
                        </div>
                    </div>
                </div> 

                @endif
                --}}
                
                @if(checkIfUserIsInMatrix(auth()->id()))
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Money Box')</h6>
                                    <h3 class="ammount theme-two">{{ showAmount(auth()->user()->balance) }}</h3>
                                </div>
                                <div class="right-content">
                                    <div class="icon"><i class="flaticon-wallet spin"></i></div>
                                </div>
                                <a href="{{route('user.deposit.index')}}">
                                    <img src="{{ asset($activeTemplateTrue . 'images/deposit.png') }}" style="width: 55px; "/> 
                                </a>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('VISA')</h6>
                                    <h3 class="ammount theme-five">{{ showAmount(auth()->user()->visa) }}</h3>
                                </div>
                            </div>
                        </div> 
                    </div> 
    
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Total Referral Commission')</h6>
                                    <h3 class="ammount theme-one">{{ showAmount($total_ref) }}</h3>
                                </div>
                                <div class="icon"><i class="flaticon-clipboards"></i></div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Total Step Out Commission')</h6>
                                    <h3 class="ammount theme-one">{{ showAmount(auth()->user()->transactions_commission()->sum('amount')) }}</h3>
                                </div>
                                <div class="icon"><i class="flaticon-money-bag"></i></div>
                            </div>
                            
                        </div> 
                    </div>
               

                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">
                                        @lang('Current Stage')
                                    </h6>
                                    <h3 class="ammount">
                                        @if(getMatrixStage(auth()->id()))
                                            <span>{{ getMatrixStage(auth()->id())->stage->name }}</span>
                                        @else
                                            <span class="text--danger">@lang('N/A')</span>
                                        @endif
                                    </h3>
                                </div>
                                <div class="right-content">
                                    <div class="icon"><i class="las la-paper-plane"></i></div>
                                </div>
                            </div>
                           
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Total Deposit')</h6>
                                    <h3 class="ammount text--base">{{ showAmount($totalDeposit) }}</h3>
                                </div>
                                <div class="icon"><i class="flaticon-save-money"></i></div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Total Withdraw')</h6>
                                    <h3 class="ammount theme-one">{{ showAmount($totalWithdraw) }}</h3>
                                </div>
                                <div class="icon"><i class="flaticon-withdraw"></i></div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Complete Withdraw')</h6>
                                    <h3 class="ammount theme-two">{{ getAmount($completeWithdraw) }}</h3>
                                </div>
                                <div class="right-content">
                                    <div class="icon"><i class="flaticon-wallet"></i></div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Pending Withdraw')</h6>
                                    <h3 class="ammount text--base">{{ getAmount($pendingWithdraw) }}</h3>
                                </div>
                                <div class="spinner-border text-primary" role="status">
                                  <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                @endif
                   {{--  <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Total Invest')</h6>
                                    <h3 class="ammount theme-one">{{ showAmount(auth()->user()->total_invest) }}</h3>
                                </div>
                                <div class="icon"><i class="flaticon-tag-1"></i></div>
                            </div>
                            
                        </div>
                    </div>
               
                   --}} 
                    
            </div>
        @endsection

        @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
            <div class="modal fade" id="kycRejectionReason">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>{{ auth()->user()->kyc_rejection_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

@push('modal')
        <div class="modal fade" id="subscribe_modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@lang('Activate Account')</h4>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <i class="las la-times"></i>
                                    </button>
                            </div>
                            <div class="modal-body">
                                <h5>@lang('Ensure you have up to the activation fee in your account ')</h5>
                            </div>

                            <div class="modal-footer">
                                <form method="post" action="{{ route('user.matrix.activate') }}">
                                    @csrf
                                    <input class="form-control form--control" class="d-none" id="plan_id" name="activate" type="hidden">
                                    <button class="btn btn--dark btn--sm" data-bs-dismiss="modal" type="button">@lang('Close')</button>
                                    <button class="btn btn--base btn--sm" type="submit"> @lang('Activated')</button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
@endpush

@push('script')
    <script>
        'use strict';
        (function($) {
           

            $('body').on('click', '#__modal_close', function(e) {
                $("#plan_info_modal").modal('hide');
            });

            $('.__subscribe').on('click', function(e) {
                let id = $(this).attr('data-id');
                $('#plan_id').attr('value', id);
                $("#subscribe_modal").modal('show');
            })
        })(jQuery)
    </script>
@endpush