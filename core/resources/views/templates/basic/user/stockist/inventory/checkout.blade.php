
@extends($activeTemplate . 'layouts.master_stockist')
@section('title', 'Checkout - Stockist Order')

@section('content')
@include($activeTemplate.'layouts.breadcrumb')
<div class="container-fluid py-4">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 fw-bold text-dark mb-1">Checkout</h1>
                        <p class="text-muted mb-0">Review your order and complete purchase</p>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-outline-success">
                            <i class="fas fa-arrow-left me-2"></i>Back to Catalog
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(empty($cart))
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body py-5">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">Your Cart is Empty</h4>
                            <p class="text-muted mb-4">Add some products to your cart before checking out.</p>
                            <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-success">
                                <i class="fas fa-shopping-cart me-2"></i>Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <!-- Order Summary -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-bag me-2 text-success"></i>
                                Order Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $subtotal = 0;
                                        @endphp
                                        @foreach($cart as $item)
                                        @php
                                            $itemTotal = $item['price'] * $item['quantity'];
                                            $subtotal += $itemTotal;
                                        @endphp
                                        <tr>
                                            <td> 
                                                <div class="d-flex align-items-center">
                                                    @if($item['image'])
                                                        <img src="{{ getImage(getFilePath('products') . '/' . $item['image'], getFilePath('products')) }}" alt="{{ $item['name'] }}" 
                                                             class="rounded me-3"
                                                             style="width: 50px; height: 50px; object-fit: cover;">

                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3"
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fas fa-box text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $item['name'] }}</h6>
                                                        <small class="text-muted">SKU: {{ $item['product_id'] }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>₦{{ number_format($item['price'], 2) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <input type="number" 
                                                           class="form-control form-control-sm quantity-update"
                                                           value="{{ $item['quantity'] }}" 
                                                           min="1" 
                                                           max="{{ $item['max_quantity'] }}"
                                                           style="width: 80px;"
                                                           data-product-id="{{ $item['product_id'] }}">
                                                </div>
                                            </td>
                                            <td>
                                                <strong class="text-success">₦{{ number_format($itemTotal, 2) }}</strong>
                                            </td>
                                            <td>
                                                <button class="btn btn-outline-danger btn-sm remove-item" 
                                                        data-product-id="{{ $item['product_id'] }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-sticky-note me-2 text-info"></i>
                                Order Notes (Optional)
                            </h5>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" id="orderNotes" rows="3" 
                                      placeholder="Add any special instructions or notes for your order..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Order Total & Payment -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 100px;">
                        <div class="card-header bg-gradient-success text-white rounded-top">
                            <h5 class="mb-0 text-center">Order Total</h5>
                        </div>
                        <div class="card-body">
                            <!-- Order Breakdown -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="fw-semibold">₦{{ number_format($subtotal, 2) }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Shipping:</span>
                                    <span class="text-success fw-semibold">FREE</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="fw-bold fs-5">Total:</span>
                                    <span class="fw-bold fs-5 text-success" id="grandTotal">
                                        ₦{{ number_format($subtotal, 2) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Wallet Information -->
                            <div class="wallet-info mb-4 p-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">
                                        <i class="fas fa-wallet me-2 text-success"></i>
                                        Wallet Balance
                                    </span>
                                    <span class="fw-bold text-success">₦{{ number_format($stockist->wallet, 2) }}</span>
                                </div>
                                
                                @if($stockist->wallet < ($subtotal))
                                    <div class="alert alert-warning small mb-0 mt-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Insufficient balance. You need ₦{{ number_format(($subtotal) - $stockist->wallet, 2) }} more.
                                    </div>
                                @else
                                    <div class="alert alert-success small mb-0 mt-2">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Sufficient balance available
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Place Order Button -->
                            <div class="d-grid">
                                @if($stockist->wallet >= ($subtotal))
                                    <button type="button" class="btn btn-success btn-lg rounded-pill py-3 fw-semibold" id="placeOrderBtn">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Place Order
                                    </button>
                                @else
                                    <button class="btn btn-secondary btn-lg rounded-pill py-3 fw-semibold" disabled>
                                        <i class="fas fa-lock me-2"></i>
                                        Insufficient Funds
                                    </button>
                                    <a href="#" class="btn btn-outline-warning mt-2 rounded-pill">
                                        <i class="fas fa-plus-circle me-2"></i>
                                        Top Up Wallet
                                    </a>
                                @endif
                            </div>
                            
                            <!-- Security Notice -->
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Secure payment · Funds deducted from wallet
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-body text-center">
                <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="text-white">Processing Order...</h5>
                <p class="text-white-50 mb-0">Please wait while we process your order</p>
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
                <h4 class="text-dark mb-3">Order Placed Successfully!</h4>
                <p class="text-muted mb-4" id="successMessage"></p>
                <div class="d-grid gap-2">
                    <a href="{{ route('user.stockist.inventory.orders') }}" class="btn btn-success rounded-pill">
                        <i class="fas fa-list me-2"></i>View Orders
                    </a>
                    <a href="{{ route('user.stockist.inventory.dashboard') }}" class="btn btn-outline-success rounded-pill">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endpush
@push('script')
<script>
$(document).ready(function() {
    let grandTotal = {{ $subtotal * 1.05 }};

    // Update quantity
    $('.quantity-update').on('change', function() {
        const productId = $(this).data('product-id');
        const quantity = $(this).val();

        $.ajax({
            url: "{{ route('user.stockist.inventory.cart.update') }}",
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error updating quantity. Please try again.');
                location.reload();
            }
        });
    });

    // Remove item
    $('.remove-item').on('click', function() {
        const productId = $(this).data('product-id');
        //alert(productId);
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            $.ajax({
                url: "{{ route('user.stockist.inventory.cart.update') }}",
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: 0,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert('Error removing item. Please try again.');
                    location.reload();
                }
            });
        }
    });

    // Place order
    $('#placeOrderBtn').on('click', function() {
        const notes = $('#orderNotes').val();
        const button = $(this);
        const originalText = button.html();

        // Show loading
        $('#loadingModal').modal('show');

        $.ajax({
            url: "{{ route('user.stockist.inventory.order.place') }}",
            method: 'POST',
            data: {
                notes: notes,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('#loadingModal').modal('hide');
                
                if (response.success) {
                    $('#successMessage').html(`
                        Your order <strong>${response.order_number}</strong> has been placed successfully!<br>
                        Order ID: <strong>#${response.order_id}</strong>
                    `);
                    $('#successModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                    button.html(originalText);
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                alert('An error occurred while placing your order. Please try again.');
                button.html(originalText);
            }
        });
    });
});
</script>
@endpush