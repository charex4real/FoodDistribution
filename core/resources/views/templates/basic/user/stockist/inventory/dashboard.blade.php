{{-- resources/views/stockist/inventory/dashboard.blade.php --}}

@extends($activeTemplate . 'layouts.master_stockist')
@section('title', 'Inventory Management - Stockist Dashboard')
@section('content')
@include($activeTemplate.'layouts.breadcrumb')
<div class="container-fluid py-4">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 fw-bold text-dark mb-1">Inventory Management</h1>
                        <p class="text-muted mb-0">Manage your product inventory and place orders</p>
                    </div>
                    <div class="text-end"> 
                        
                        <a href="{{ route('user.stockist.inventory.checkout') }}" class="btn btn-outline-success position-relative pull-right">
                            <i class="fas fa-shopping-cart me-2"></i>Cart
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount">
                                        {{ count($cart) }}
                                    </span>
                        </a>
                        <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-outline-success  rounded-pill">
                            <i class="fas fa-shopping-cart me-2"></i>Order Products
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 bg-gradient-primary text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1"> 
                                <h4 class="fw-bold mb-0">{{ $stats['total_products'] }}</h4>
                                <small>Total Products</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-boxes fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-gradient-warning text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ $stats['low_stock_count'] }}</h4>
                                <small>Low Stock</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-gradient-primary text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ $stats['out_of_stock_count'] }}</h4>
                                <small>Out of Stock</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-times-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-gradient-success text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">₦{{ number_format($stats['wallet_balance'], 2) }}</h4>
                                <small>Wallet Balance</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-wallet fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Current Inventory -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-boxes me-2 text-primary"></i>
                            Current Inventory
                            
                        </h5>
                       
                    </div>
                    <div class="card-body">
                        @if($inventory->isEmpty())
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Inventory Yet</h5>
                                <p class="text-muted mb-4">You haven't added any products to your inventory yet.</p>
                                <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-success">
                                    <i class="fas fa-shopping-cart me-2"></i>Order Products
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Current Stock</th>
                                            <th>Stock Levels</th>
                                            <th>Status</th>
                                           
                                            <!-- <th>Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($inventory as $item)
                                        <tr>
                                            <td> 
                                                <div class="d-flex align-items-center">
                                                    @if($item->product->thumbnail)
                                                    <img src="{{ getImage(getFilePath('products') . '/' . $item->product->thumbnail, getFilePath('products')) }}" 
                                                         alt="{{ $item->product->name }}" 
                                                         class="rounded me-3"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3"
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-box text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                        <small class="text-muted">SKU: {{ $item->product->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold {{ $item->is_low_stock ? 'text-warning' : 'text-dark' }}">
                                                    {{ $item->quantity }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    Min: {{ $item->min_stock_level }}<br>
                                                    Max: {{ $item->max_stock_level }}
                                                </small>
                                            </td>
                                            <td>
                                                @switch($item->stock_status)
                                                    @case('out_of_stock')
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                        @break
                                                    @case('low_stock')
                                                        <span class="badge bg-warning">Low Stock</span>
                                                        @break
                                                    @case('over_stock')
                                                        <span class="badge bg-info">Over Stock</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-success">In Stock</span>
                                                @endswitch
                                            </td>
                                            {{--
                                            <td>
                                                <strong>₦{{ number_format($item->unit_cost, 2) }}</strong>
                                                <br/>
                                                <strong>₦{{ number_format($item->total_value, 2) }}</strong>
                                            </td>

                                            
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editInventoryModal"
                                                        data-inventory-id="{{ $item->id }}"
                                                        data-min-level="{{ $item->min_stock_level }}"
                                                        data-max-level="{{ $item->max_stock_level }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>

                                            --}}
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Alerts -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2 text-warning"></i>
                            Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-success rounded-pill text-start">
                                <i class="fas fa-shopping-cart me-2"></i>Order Products
                            </a> 
                            <a href="{{ route('user.stockist.inventory.orders') }}" class="btn btn-outline-success rounded-pill text-start">
                                <i class="fas fa-history me-2"></i>View Order History
                            </a>
                            <button class="btn btn-outline-success rounded-pill text-start">
                                <i class="fas fa-chart-line me-2"></i>View Reports
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                @if($lowStockItems->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-3 mb-4 border-warning">
                    <div class="card-header bg-warning text-white py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Low Stock Alerts
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($lowStockItems->take(3) as $item)
                        <div class="alert alert-warning alert-sm d-flex align-items-center mb-2 py-2">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div class="flex-grow-1">
                                <strong>{{ $item->product->name }}</strong><br>
                                <small>Stock: {{ $item->quantity }} (Min: {{ $item->min_stock_level }})</small>
                            </div>
                        </div>
                        @endforeach
                        @if($lowStockItems->count() > 3)
                        <div class="text-center">
                            <small class="text-muted">+{{ $lowStockItems->count() - 3 }} more items</small>
                        </div>
                        @endif
                        <div class="text-center mt-2">
                            <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-warning btn-sm">
                                Reorder Now
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Out of Stock Alerts -->
                @if($outOfStockItems->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-3 border-danger">
                    <div class="card-header bg-danger text-white py-3">
                        <h6 class="mb-0">
                            <i class="fas fa-times-circle me-2"></i>
                            Out of Stock
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($outOfStockItems->take(3) as $item)
                        <div class="alert alert-danger alert-sm d-flex align-items-center mb-2 py-2">
                            <i class="fas fa-times me-2"></i>
                            <div class="flex-grow-1">
                                <strong>{{ $item->product->name }}</strong><br>
                                <small>Completely out of stock</small>
                            </div>
                        </div>
                        @endforeach
                        <div class="text-center">
                            <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-danger btn-sm">
                                Restock Now
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>


@endsection
@push('modal')


<!-- Edit Inventory Modal -->
<div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModal" aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock Levels</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editInventoryForm">
                    @csrf
                    <input type="hidden" name="inventory_id" id="editInventoryId">
                    
                    <div class="mb-3">
                        <label class="form-label">Minimum Stock Level</label>
                        <input type="number" class="form-control" name="min_stock_level" id="editMinLevel" min="0" required>
                        <small class="text-muted">Alert when stock falls below this level</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Maximum Stock Level</label>
                        <input type="number" class="form-control" name="max_stock_level" id="editMaxLevel" min="1" required>
                        <small class="text-muted">Recommended maximum stock level</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveInventoryLevels">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endpush
@push('script')
<script>
$(document).ready(function() {
    // Edit Inventory Modal
    $('#editInventoryModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const inventoryId = button.data('inventory-id');
        const minLevel = button.data('min-level');
        const maxLevel = button.data('max-level');
        
        $('#editInventoryId').val(inventoryId);
        $('#editMinLevel').val(minLevel);
        $('#editMaxLevel').val(maxLevel);
    });

    // Save inventory levels
    $('#saveInventoryLevels').on('click', function() {
        const formData = $('#editInventoryForm').serialize();
        
        $.ajax({
            url: "{{ route('user.stockist.inventory.levels.update') }}",
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editInventoryModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Error updating inventory levels.');
            }
        });
    });
});
</script>
@endpush