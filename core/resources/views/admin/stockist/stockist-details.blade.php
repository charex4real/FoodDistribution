@extends('admin.layouts.app')

@section('title', $stockist->business_name . ' - Details')

@section('panel')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.stockist.dashboard') }}" class="btn btn-light btn-circle btn-sm mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-0 text-gray-800">{{ $stockist->business_name }}</h1>
                <p class="text-muted mb-0">Stockist Details & Analytics</p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.stockist.wallet-page', $stockist) }}" class="btn btn-warning btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-wallet"></i>
                </span>
                <span class="text">Top-up Wallet</span>
            </a>
            <a href="{{ route('admin.stockist.dashboard') }}" class="btn btn-success">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <!-- <button class="btn btn-info btn-icon-split ml-2">
                <span class="icon text-white-50">
                    <i class="fas fa-edit"></i>
                </span>
                <span class="text">Edit</span>
            </button> -->
        </div>
    </div>




    <!-- Statistics Cards -->
    <div class="row">
        <!-- Wallet Balance -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Wallet Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₦{{ number_format($stockist->wallet, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalProductsCount) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unique Products -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Unique Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $uniqueProducts }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Recent Activity (7d)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $recentActivity }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Business Information -->
        <div class="col-lg-6">
            <!-- Business Profile Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-success  py-3">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-building mr-2"></i>Business Profile
                    </h6>
                </div>
                <div class="card-body">
                    <!-- In stockist-details.blade.php - Add this in the business profile card -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="business-info-item mb-3">
                                <label class="font-weight-bold text-success">Business Name</label>
                                <p class="mb-1">{{ $stockist->business_name }}</p>
                            </div>
                            <div class="business-info-item mb-3">
                                <label class="font-weight-bold text-success">Store Type</label>
                                <p class="mb-1">
                                    @if($stockist->store_type == 1)
                                        <span class="badge badge--success badge-pill p-2">Mega Store</span>
                                    @else
                                        <span class="badge badge--info badge-pill p-2">Mini Store</span>
                                    @endif
                                </p>
                            </div>
                            <div class="business-info-item mb-3">
                                <label class="font-weight-bold text-success">State</label>
                                <p class="mb-1">
                                    <i class="fas fa-map-marker-alt text-danger mr-2"></i>
                                    {{ $stockist->state_name }}
                                </p>
                            </div>
                            <div class="business-info-item mb-3">
                                <label class="font-weight-bold text-success">Business Email</label>
                                <p class="mb-1">
                                    <i class="fas fa-envelope text-success mr-2"></i>
                                    <a href="mailto:{{ $stockist->business_email }}" class="text-decoration-none">
                                        {{ $stockist->business_email }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Rest of the existing fields -->
                            <div class="business-info-item mb-3">
                                <label class="font-weight-bold text-success">Business Phone</label>
                                <p class="mb-1">
                                    <i class="fas fa-phone text-success mr-2"></i>
                                    <a href="tel:{{ $stockist->business_phone }}" class="text-decoration-none">
                                        {{ $stockist->business_phone }}
                                    </a>
                                </p>
                            </div>
                            <div class="business-info-item mb-3">
                                <label class="font-weight-bold text-success">Registration Number</label>
                                <p class="mb-1">
                                    {{ $stockist->business_registration_number ?? 'Not Provided' }}
                                </p>
                            </div>
                            <!-- ... rest of existing fields ... -->
                        </div>
                    </div>

                    @if($stockist->business_description)
                    <div class="business-info-item mt-4">
                        <label class="font-weight-bold text-success">Business Description</label>
                        <div class="bg-light p-3 rounded mt-2">
                            <p class="mb-0 text-justify">{{ $stockist->business_description }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- User Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-success py-3">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-user mr-2"></i>User Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="user-avatar bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-user text-white fa-2x"></i>
                        </div>
                        <h5 class="font-weight-bold text-gray-800">{{ $stockist->user->fullname ?? 'N/A' }}</h5>
                        <p class="text-muted">@ {{ $stockist->user->username ?? 'N/A' }}</p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="user-info-item mb-3">
                                <label class="font-weight-bold text-info">Email</label>
                                <p class="mb-1">{{ $stockist->user->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="user-info-item mb-3">
                                <label class="font-weight-bold text-info">Member Since</label>
                                <p class="mb-1">{{ $stockist->user->created_at->format('M d, Y') ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="user-info-item">
                        <label class="font-weight-bold text-info">User Balance</label>
                        <p class="mb-0 h5 text-success">₦{{ number_format($stockist->user->balance ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Products & Activity -->
        <div class="col-lg-6">
            <!-- Products Inventory Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-success  py-3">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-boxes mr-2 "></i>Products Inventory
                    </h6>
                </div>
                <div class="card-body">
                    @if($stockist->stockistStores->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light text-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockist->stockistStores->take(5) as $store)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="product-icon bg-success rounded-circle mr-3">
                                                    <i class="fas fa-box text-white"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $store->product->name ?? 'Unknown Product' }}</strong>
                                                    <br>
                                                    <small class="text-muted">SKU: {{ $store->product->sku ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge--primary badge-pill p-2">
                                                {{ number_format($store->quantity) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success font-weight-bold">
                                                ₦{{ number_format(($store->product->price ?? 0) * $store->quantity, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($store->quantity > 0)
                                                <span class="badge badge--success">In Stock</span>
                                            @else
                                                <span class="badge badge--warning">Out of Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($stockist->stockistStores->count() > 5)
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-sm btn-outline-success">
                                View All {{ $stockist->stockistStores->count() }} Products
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No products in inventory</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-success  py-3">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-exchange-alt mr-2"></i>Recent Transactions
                    </h6>
                </div>
                <div class="card-body">
                    @if($stockist->sktransactions && $stockist->sktransactions->count() > 0)
                        <div class="transaction-list">
                            @foreach($stockist->sktransactions->take(5) as $transaction)
                            <div class="transaction-item d-flex align-items-center justify-content-between py-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="transaction-icon bg-{{ $transaction->type == 'credit' ? 'success' : 'danger' }} rounded-circle mr-3">
                                        <i class="fas fa-{{ $transaction->type == 'credit' ? 'plus' : 'minus' }} text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">{{ $transaction->description }}</h6>
                                        <small class="text-muted">{{ $transaction->created_at->format('M d, Y h:i A') }}</small>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-weight-bold text-{{ $transaction->type == 'credit' ? 'success' : 'danger' }}">
                                        {{ $transaction->type == 'credit' ? '+' : '-' }}₦{{ number_format($transaction->amount, 2) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">Balance: ₦{{ number_format($transaction->balance_after, 2) }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent transactions</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions Card -->
           {{--  
           <div class="card shadow">
                <div class="card-header bg-gradient-secondary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt mr-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <a href="{{ route('admin.stockist.wallet-page', $stockist) }}" class="btn btn-success btn-circle btn-lg mb-2">
                                <i class="fas fa-wallet"></i>
                            </a>
                            <p class="small mb-0">Top-up Wallet</p>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-info btn-circle btn-lg mb-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <p class="small mb-0">Edit Profile</p>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-{{ $stockist->is_active ? 'danger' : 'success' }} btn-circle btn-lg mb-2">
                                <i class="fas fa-{{ $stockist->is_active ? 'pause' : 'play' }}"></i>
                            </button>
                            <p class="small mb-0">{{ $stockist->is_active ? 'Deactivate' : 'Activate' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            --}}
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* Custom Styles for Beautiful Design */
.business-info-item label {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    display: block;
}

.business-info-item p {
    font-size: 1rem;
    color: #6c757d;
}

.user-avatar {
    width: 80px;
    height: 80px;
}

.product-icon, .transaction-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #4e73df 0%, #224abe 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(45deg, #1cc88a 0%, #13855c 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(45deg, #36b9cc 0%, #258391 100%) !important;
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #f6c23e 0%, #dda20a 100%) !important;
}

.bg-gradient-secondary {
    background: linear-gradient(45deg, #858796 0%, #60616f 100%) !important;
}

.card {
    border: none;
    border-radius: 15px;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.btn-circle {
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-icon-split {
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: stretch;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.btn-icon-split .icon {
    width: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-icon-split .text {
    padding: 0.375rem 0.75rem;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.transaction-item {
    transition: background-color 0.2s ease;
}

.transaction-item:hover {
    background-color: #f8f9fc;
    border-radius: 8px;
}

.badge--pill {
    border-radius: 10rem;
    padding: 0.5rem 1rem;
}

.table th {
    border-top: none;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}
</style>
@endpush

@push('script')
<script>
$(document).ready(function() {
    // Add smooth animations
    $('.card').addClass('animate__animated animate__fadeInUp');
    
    // Stagger animation for cards
    $('.card').each(function(index) {
        $(this).css('animation-delay', (index * 0.1) + 's');
    });

    // Hover effects for quick action buttons
    $('.btn-circle').hover(
        function() {
            $(this).addClass('animate__animated animate__pulse');
        },
        function() {
            $(this).removeClass('animate__animated animate__pulse');
        }
    );
});
</script>
@endpush