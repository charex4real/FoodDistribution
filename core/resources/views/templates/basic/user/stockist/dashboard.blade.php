
@extends($activeTemplate . 'layouts.master_stockist')
@section('title', 'Stockist Dashboard')
@section('content')
 @include($activeTemplate.'layouts.breadcrumb')
<div class="container-fluid py-4">
    <div class="container">
        <!-- Header -->

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                       <!--  <h1 class="h3 fw-bold text-dark mb-1">Stockist Dashboard</h1> -->
                        <p class="mb-0 fw-bold">Welcomeee back, {{ auth()->user()->fullname }}</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success fs-6">Active Stockist</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">

            <div class="col-sm-6 col-md-3 mb-2">
                <div class="card border-0 bg-gradient-success text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ showAmount(auth()->user()->stockist->wallet) }}</h4>
                                <small>Stockist Rebate</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="card border-0 bg-gradient-primary text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ $stats['total_redemptions'] }}</h4>
                                <small>Total Redemptions</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-receipt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="card border-0 bg-gradient-warning text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ $stats['today_redemptions'] }}</h4>
                                <small>Today's Redemptions</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-2 col-sm-6 col-md-3">
                <div class="card border-0 bg-gradient-info text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-0">{{ showAmount($stats['total_amount']) }}</h5>
                                <small>Total Processed</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-coins fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Redeem Invoice Section -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-qrcode me-2 text-primary"></i>
                            Redeem Invoice
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="redeemInvoiceForm">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Invoice Code</label>
                                <input type="text" id="invoiceCodeInput" class="invoiceC form-control form-control-lg" 
                                       name="invoice_code" placeholder="Enter invoice code" required
                                       />
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-success btn-lg rounded-pill px-5">
                                    <i class="fas fa-search me-2"></i>Verify Invoice
                                </button>
                            </div>
                        </form>
 
                        <!-- Invoice Details -->
                        <div id="invoiceDetails" class="mt-4 d-none">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0 text-white">Invoice Verified</h6>
                                </div>
                                <div class="card-body">
                                    <div id="invoiceItems"></div>
                                    <form id="processRedemptionForm" class="mt-3">
                                        <div id="redemptionItems"></div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Notes (Optional)</label>
                                            <textarea class="form-control" name="notes" rows="2" 
                                                      placeholder="Add any notes about this redemption..."></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                                <i class="fas fa-check me-2"></i>Process Redemption
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Redemptions -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2 text-info"></i>
                            Recent Redemptions
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($recentRedemptions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentRedemptions as $redemption)
                                        <tr>
                                            <td>
                                                <small class="fw-semibold">{{ $redemption->invoice->invoice_code }}</small>
                                            </td>
                                            <td>{{ $redemption->user->username }}</td>
                                            <td>₦{{ number_format($redemption->total_amount, 2) }}</td>
                                            <td>{{ $redemption->created_at->format('M j, H:i') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('user.stockist.history') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                                    View Full History
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No redemptions yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2 text-warning"></i>
                            Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('user.stockist.history') }}" class="btn btn-outline-success rounded-pill text-start">
                                <i class="fas fa-history me-2"></i>Redemption History
                            </a>
                            <button class="btn btn-outline-success rounded-pill text-start" onclick="clearForm()">
                                <i class="fas fa-broom me-2"></i>Clear Form
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            Redemption Guide
                        </h6>
                    </div>
                    <div class="card-body">
                        <ol class="small ps-3">
                            <li class="mb-2">Enter the customer's invoice code</li>
                            <li class="mb-2">Verify the invoice details</li>
                            <li class="mb-2">Adjust quantities based on available stock</li>
                            <li class="mb-2">Process partial or full redemption</li>
                            <li class="mb-2">Provide remaining items to customer</li>
                        </ol>
                        <div class="alert alert-warning small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Customers can redeem remaining items at other stockists
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('modal')
<!-- Success Modal -->

<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModal" aria-hidden="true">    
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h4 class="text-dark mb-3">Redemption Successful!</h4>
                <p class="text-muted mb-4" id="successMessage"></p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success rounded-pill" data-bs-dismiss="modal">Continue</button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="clearForm()">Process Another</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="invoiceAlreadyRedeem" tabindex="-1" aria-labelledby="invoiceAlreadyRedeem" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h4 class="text-dark mb-3">Note !!!</h4>
                <p class="text-muted mb-4" id="copiedInvoiceText"></p>
                <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">Continue</button>
            </div>
        </div>
    </div>
</div>


@endpush
@push('script')
<script>
function clearForm() {
       // alert('dd');
        $('#redeemInvoiceForm')[0].reset();
        $('#invoiceDetails').addClass('d-none');
        //$('#invoiceCodeInput').focus();
        $('.invoiceC').focus();

    }
$(document).ready(function() {



    $('#redeemInvoiceForm').on('submit', function(e) {
        e.preventDefault();
        var v = $('#invoiceCodeInput').val();
        var invoiceCode = $('.invoiceC').val();
       
        //alert(invoiceCode);
        $.ajax({
            url: "{{ route('user.stockist.verify.invoice') }}",
            method: 'POST',
            data: {
                invoice_code: invoiceCode,
                _token: "{{ csrf_token() }}"
            }, 
            beforeSend: function() {
                //$('.invoiceC')[0].reset();
                //$('.invoiceC').addClass('d-none');
            },
            success: function(response) {
                if (response.success) {
                    displayInvoiceDetails(response);
                    //alert(response);
                } else {
                    var msg = response.message;
                    //alert(response.message);

                    $('#copiedInvoiceText').html(msg);
                    $('#invoiceAlreadyRedeem').modal('show');
                  
                }
            },
            error: function(xhr) {
                alert('Error verifying invoice. Please try again.');
            }
        });
    });

    $('#processRedemptionForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('invoice_code', $('.invoiceC').val());
        formData.append('_token', "{{ csrf_token() }}");

        $.ajax({
            url: "{{ route('user.stockist.process.redemption') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showSuccessModal(response);
                } else {

                    var msg = response.message;
                    $('#copiedInvoiceText').html(msg);
                    $('#invoiceAlreadyRedeem').modal('show');
                    //alert(response.message);
                }
            },
            error: function(xhr) {
                alert('Error processing redemption. Please try again.');
            }
        });
    });

    function displayInvoiceDetails(response) {
        const { invoice, remaining_items, original_items } = response;
         //alert(original_items);

        let itemsHtml = `
            <h6 class="fw-bold mb-3">Order Items</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Original Qty</th>
                            <th>Redeemed</th>
                            <th>Remaining</th>
                            <th>Redeem Qty</th>
                        </tr>
                    </thead>
                    <tbody>`;

        original_items.forEach(item => {
            const remaining = remaining_items[item.product_id] || 0;
            const redeemed = item.quantity - remaining;
            
            itemsHtml += `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>${redeemed}</td>
                    <td>${remaining}</td>
                    <td>
                        <input type="number" 
                               class="form-control form-control-sm redemption-qty" 
                               name="redeemed_items[${item.product_id}]" 
                               value="${remaining}" 
                               min="0" 
                               max="${remaining}"
                               style="width: 80px;">
                    </td>
                </tr>`;
        });

        itemsHtml += `</tbody></table></div>`;
        //$('#invoiceItems').html(itemsHtml);
        $('#redemptionItems').html(itemsHtml);
        $('#invoiceDetails').removeClass('d-none');
    }

    function showSuccessModal(response) {
        const message = response.remaining_items && Object.keys(response.remaining_items).length > 0 
            ? 'Partial redemption processed successfully. Customer can redeem remaining items at other stockists.'
            : 'Full redemption processed successfully. All items have been redeemed.';
        
        $('#successMessage').text(message);
        $('#successModal').modal('show');
    }
});
</script>
@endpush