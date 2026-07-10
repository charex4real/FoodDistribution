
@extends($activeTemplate . 'layouts.master_stockist')

@section('title', 'Product Catalog - Stockist Inventory')

@section('content')
@include($activeTemplate.'layouts.breadcrumb')
<div class="container-fluid py-4">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 fw-bold text-dark mb-1">Product Catalog</h1>
                        <p class="text-muted mb-0">Browse and order products for your inventory</p>
                    </div>
                    <div class="text-end">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Cart Icon -->
                            <div class="position-relative">
                                <a href="{{ route('user.stockist.inventory.checkout') }}" class="btn btn-outline-success position-relative">
                                    <i class="fas fa-shopping-cart me-2"></i>Cart
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount">
                                        {{ count($cart) }}
                                    </span>
                                </a>
                            </div>
                            <!-- Wallet Balance -->
                            <div class="bg-light rounded-pill px-3 py-2">
                                <small class="text-muted">Wallet:</small>
                                <strong class="text-success">₦{{ number_format($stockist->wallet, 2) }}</strong>
                            </div>
                            <a href="{{ route('user.stockist.inventory.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row">
            @forelse($products as $productPrice)
            @if($productPrice->product->status != 0 && $productPrice->product->quantity > 0)
            <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
                <div class="card product-card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-0">
                        <!-- Product Image -->
                        <div class="product-image position-relative">
                            @if($productPrice->product->thumbnail)
                                <img src="{{ getImage(getFilePath('products') . '/' . $productPrice->product->thumbnail, getFileSize('products')) }}" 
                                     alt="{{ $productPrice->product->name }}" 
                                     class="card-img-top"
                                     style="height: 200px; object-fit: cover;">

                                   {{--  <img src="{{ getImage(getFilePath('products') . '/' . $productPrice->product->thumbnail, getFileSize('products')) }}" alt="products"> --}}

                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center"
                                     style="height: 200px;">
                                    <i class="fas fa-box fa-3x text-muted"></i>
                                </div>
                            @endif
                            
                            <!-- Stock Badge -->
                            <div class="position-absolute top-0 end-0 m-3">
                                @if($productPrice->product->quantity > 0)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>In Stock
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times me-1"></i>Out of Stock
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-3">
                            <h6 class="fw-bold text-dark mb-2">{{ $productPrice->product->name }}</h6>
                            <p class="text-muted small mb-3" style="min-height: 40px;">
                                {{ Str::limit($productPrice->product->description, 80) }}
                            </p>

                            <!-- Price -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="text-success mb-0">{{ $productPrice->formatted_price }}</h5>
                                    
                                </div>
                                <div class="text-end">
                                    
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>In Stock
                                    </span>
                                    {{-- <strong class="text-dark">{{ $productPrice->product->quantity }} units</strong> --}}
                                </div>
                            </div>
                            
                            <!-- Order Form -->
                            @if($productPrice->product->is_available)
                            <form class="add-to-cart-form" data-product-id="{{ $productPrice->product->id }}">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Quantity</label>
                                        <input type="number" 
                                               class="form-control form-control-sm quantity-input"
                                               name="quantity"
                                               value="{{ $productPrice->product->min_order_quantity }}"
                                               min="{{ $productPrice->product->min_order_quantity }}"
                                               max="{{ min($productPrice->product->stock_quantity, $productPrice->product->max_order_quantity) }}"
                                               required>
                                        <small class="text-muted">
                                            Min: {{ $productPrice->product->min_order_quantity }}, 
                                            Max: {{ min($productPrice->product->stock_quantity, $productPrice->product->max_order_quantity) }}
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-success btn-sm w-100 add-to-cart-btn">
                                            <i class="fas fa-cart-plus me-1"></i>Add
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @else
                            <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                <i class="fas fa-times me-1"></i>Out of Stock
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Products Available</h4>
                        <p class="text-muted">There are currently no products available for ordering.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        
        @if($products->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                                </small>
                            </div>
                            <div>
                                {{ $products->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
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
                <h4 class="text-dark mb-3">Added to Cart!</h4>
                <p class="text-muted mb-4" id="successMessage">Product has been added to your cart successfully.</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success rounded-pill" data-bs-dismiss="modal">Continue Shopping</button>
                    <a href="{{ route('user.stockist.inventory.checkout') }}" class="btn btn-outline-success rounded-pill">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endpush
@push('style')
    <style>
        .product-card {
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .quantity-input {
            border-radius: 8px;
        }

        .add-to-cart-btn {
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-1px);
        }

        .badge {
            font-weight: 500;
        }

        .card-img-top {
            border-radius: 12px 12px 0 0;
        }
    </style>
@endpush

@push('script')
<script>
$(document).ready(function() {
    // Add to cart functionality
    $('.add-to-cart-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const productId = form.data('product-id');
        const quantity = form.find('.quantity-input').val();
        const button = form.find('.add-to-cart-btn');
        const originalText = button.html();

        // Show loading state
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Adding...');


        $.ajax({
            url: "{{ route('user.stockist.inventory.cart.add') }}",
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                _token: "{{ csrf_token() }}"
            }, 
            success: function(response) {
                if (response.success) {
                    // Update cart count
                    $('#cartCount').text(response.cart_count);
                    
                    // Show success message
                    $('#successMessage').text(response.message);
                    $('#successModal').modal('show');
                    
                    // Reset form
                    form.find('.quantity-input').val(form.find('.quantity-input').attr('min'));
                } else {
                    alert('Error: ' + response.message);
                }
                
                button.prop('disabled', false).html(originalText);
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
                button.prop('disabled', false).html(originalText);
            }
        });
    });

    // Quantity validation
    $('.quantity-input').on('change', function() {
        const input = $(this);
        const min = parseInt(input.attr('min'));
        const max = parseInt(input.attr('max'));
        let value = parseInt(input.val());

        if (value < min) {
            input.val(min);
        } else if (value > max) {
            input.val(max);
        }
    });

    // Auto-update cart count periodically
    function updateCartCount() {
        $.get("{{ route('user.stockist.inventory.cart.get') }}", function(response) {
            $('#cartCount').text(response.count);
        });
    }

    // Update cart count every 30 seconds
    setInterval(updateCartCount, 30000);
});
</script>
@endpush