@extends($activeTemplate . 'layouts.master')
@section('content')
 @include($activeTemplate.'layouts.breadcrumb')
    <div class="container">
        <div class="row"> 
            <div class="row justify-content-center g-3">
                <div class="row justify-content-center g-3">   
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Stockist Wallet')</h6>
                                    <h3 class="ammount theme-two">{{ showAmount($wallet, 2) }}</h3>
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
                                    <h6 class="title">@lang('Total Orders')</h6>
                                    <h3 class="ammount theme-one">{{ returnStockistOrder(auth()->id())->count() }}</h3>
                                </div>
                                <div class="icon"><i class="flaticon-clipboards"></i></div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-4">
                        <div class="dashboard-item">
                            <div class="dashboard-item-header">
                                <div class="header-left">
                                    <h6 class="title">@lang('Total Orders')</h6>
                                    <h3 class="ammount theme-two">{{ returnStockistOrder(auth()->id())->count() }}</h3>
                                </div>
                                <div class="right-content">
                                    <div class="icon"><i class="flaticon-wallet"></i></div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                </div>
                
                
               
            </div>

        </div>


    </div>
<div class="b-example-divider"></div><br>
<!-- Product  -->
   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2" style="color: #135D26;">{{ __('Sales') }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group me-2">
            
          </div>
        </div>
    </div>
  <div class="container col-lg-12 col-xl-12 col-xxl-12 px-1 py-4">
    <div class="row align-items-center g-lg-5 py-2">
      <div class="col-lg-7 text-center text-lg-start">
        <h1 class="display-8 fw-bold mb-32">Stockist sales center</h1>
        <p class="col-lg-10 fs-7 py-2">Below is an example form built entirely with Bootstrap’s form controls. Each required form group has a validation state that can be triggered by attempting to submit the form without completing it.</p>
      </div>
      <div class="col-md-10 mx-auto col-lg-5">
        
        
          <div class="form-floating mb-3">
            <input type="text" class="form-control codeOrder" id="floatingInput" name="purchasecode" placeholder="Purchase Cod">
            <label for="floatingInput">Purchase Code</label>
            <span id="wrongCode" class="text-danger"></span>
          </div>
          
          
          <button type="button" class="btn btn-success checkOrder" data-bs-toggle="modal">
            Check Status
          </button>
          <hr class="my-4">
          <small class="text-muted">By clicking proccess you agree to adhere to our company privacy and policy.</small>
        </form>
      </div>
    </div>
  </div>

  <!-- Product  -->
   <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2" style="color: #135D26;">{{ __('Restock ') }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group me-2">
            
          </div>
        </div>
    </div> 
                @foreach($stockist_store as $store)
    
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
                        <div class="product-item h-100">
                            <div class="product-thumb">
                                <img src="{{ getImage(getFilePath('products') . '/' . $store->product->thumbnail, getFileSize('products')) }}" alt="products">
                               
                            </div>
                            <div class="product-content">
                                <h6 class="product-title">
                                    <a>{{ __(shortDescription($store->product->name, 35)) }}</a>
                                </h6>
                                
                                @if ($store->quantity >= 20)
                                    <span class="product-availablity text--success">@lang('STOCK'): {{ $store->quantity }}</span>
                                @else
                                    <span class="product-availablity text--danger">@lang('out stock')</span>
                                @endif
                               
                                <div class="product-price">
                                    <span class="current-price">{{ showAmount($store->product->price) }}</span>
                                </div>
                                <a class="re_stock cmn--btn-2" stock_id="{{ $store->id }}" prod_id="{{ $store->product_id }}" id="reStock" 
                                    >@lang('RE-stock')</a>
                                    <br/>

                            </div>
                        </div>
                    </div>
    
                @endforeach

@endsection


@push('modal')
    <div class="modal fade" id="exampleModalCenteredScrollable" tabindex="-1" aria-labelledby="exampleModalCenteredScrollableTitle" aria-hidden="true">
      
      <div class="modal-dialog" role="document">
        <div class="modal-content rounded-4 shadow">
          <div class="modal-body p-5">
            <h2 class="fw-bold mb-0">Confirm</h2>

            <ul class="d-grid gap-4 my-5 list-unstyled">
              <li class="d-flex gap-4">
                <svg class="bi text-muted flex-shrink-0" width="48" height="48"><use xlink:href="#check2-circle"/></svg>
                <div>
                  <h5 class="mb-0"><span id="productName"></span></h5>
                  <span id="productDesc"></span>
                </div>
              </li>
              
              <ul class="list-group mb-3">
                  <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                      <h6 class="my-0">Quantity</h6>
                    </div>
                    <span class="text-muted"><span id="productQty"></span></span>

                  </li>
                  <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                      <h6 class="my-0">Price</h6>
                    </div>
                    <span class="text-muted"><span id="productprice"></span></span>

                  </li>
                  <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                      <h6 class="my-0">Status</h6>
                    </div>
                    <span class="text-muted"><span id="orderStatus"></span></span>

                  </li>

                  <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                      <h6 class="my-0">Total</h6>
                    </div>
                    <span class="text-muted"><span id="productTotal"></span></span>
                  </li>
              </ul>
              
            </ul>
            <form method="post" action="{{ route('user.purchaseDone') }}">
                @csrf
                <input class="form-control form--control" class="d-none" id="userId" name="userId" type="hidden">
                 <input class="form-control form--control" class="d-none" id="order_code" name="order_code" type="hidden">
                  <input class="form-control form--control" class="d-none" id="productId" name="productId" type="hidden">
                 
                <button class="btn btn--danger btn--sm" data-bs-dismiss="modal" type="button">@lang('Close')</button>
                
                <button class="btn btn--base btn--sm" type="submit"> @lang('Proceed')</button>
            </form>
             
            
          </div>
        </div>
      </div>
    </div>



<div class="modal fade" id="reStockModal" tabindex="-1" aria-labelledby="reStockmodal" aria-hidden="true">
  
  <div class="modal-dialog" role="document">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-body p-5">
        <h2 class="fw-bold mb-0">Restock</h2>

        <br>
        <form method="post" action="{{ route('user.restock') }}">
            @csrf
            <input class="form-control" class="d-none" id="prod_id" name="prod_id" type="hidden">
            
            <input class="form-control" class="d-none" id="stock_id" name="stock_id" type="hidden">

             <label>Please type the quantity</label>
              <input class="form-control w-50" class="d-none" id="qty" name="qty" type="text" value="200"> <br/>
         
             
            <button class="btn btn--danger btn--sm" data-bs-dismiss="modal" type="button">@lang('Close')</button>
            
            <button class="btn btn--base btn--sm" type="submit"> @lang('Proceed')</button>
        </form>
       
      </div>
    </div>
  </div>
</div>
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  
  <symbol id="check2-circle" viewBox="0 0 16 16">
    <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0z"/>
    <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l7-7z"/>
  </symbol>

</svg>

        

@endpush


@push('script')
    <script>
        'use strict';
        (function($) {

            function checkCode(value){

                var url = '{{ route('user.checkCode') }}';
                var token = '{{ csrf_token() }}';
                    var data = {
                        code: value,
                        _token: token
                    }                

                $.post(url, data, function(response) {
                    if (response.data != false) {
                       var sts = '';
                        var { order_code, price, qty, name, description, userId, productId, total, status } = response.data;
                      

                        $('#order_code').attr('value', order_code);
                        $('#userId').attr('value', userId);
                        $('#productId').attr('value', productId);
                        
                        $('#productName').html(name);
                        $('#productDesc').html(description);
                        $('#productQty').html(qty);
                        $('#productprice').html(price);
                        $('#productTotal').html(total);

                        
                        if (status == 1) {
                            var sts = 'Product Delivered';
                        }
                        if (status == 0) {
                            var sts = 'Pending';
                        }
                        if (status == 2) {
                            var sts = 'Order Cancelled';
                        }
                        $('#orderStatus').html(sts);

                       $('#exampleModalCenteredScrollable').modal('show');
                       
                    } else {
                        
                        $('#wrongCode').html('Invalid / Used code');
                    }
                });
            }
            $('.checkOrder').on('click', function(e) {
                var v = $('.codeOrder').val();
                
                if(v.length == 10){
                    checkCode(v);
                }
                
            });

            // REstock section
            $('.re_stock').on('click', function(e) {
               
                let prod_id = $(this).attr('prod_id');
                var stock_id = $(this).attr('stock_id');
              
              //alert(stock_id);

              $('#prod_id').attr('value', prod_id);
              $('#stock_id').attr('value', stock_id);
               // var token = '{{ csrf_token() }}';
                $('#reStockModal').modal('show');

            });
        })(jQuery)
    </script>
@endpush
 
@push('css-styles')
    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .b-example-divider {
        height: 3rem;
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
      }

      .b-example-vr {
        flex-shrink: 0;
        width: 1.5rem;
        height: 100vh;
      }

      .bi {
        vertical-align: -.125em;
        fill: currentColor;
      }
 
      .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
      }

      .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
      }
    </style>
@endpush

       
