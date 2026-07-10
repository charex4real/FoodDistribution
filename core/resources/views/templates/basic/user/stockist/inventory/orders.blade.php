
@extends($activeTemplate . 'layouts.master_stockist')
@section('title', 'Order History - Stockist Inventory')
@section('content')
@include($activeTemplate.'layouts.breadcrumb')
<div class="container-fluid py-4">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 fw-bold text-dark mb-1">Order History</h1>
                        <p class="text-muted mb-0">Track and manage your product orders</p>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-outline-success">
                            <i class="fas fa-plus me-2"></i>New Order
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
                                <h4 class="fw-bold mb-0">{{ $orders->total() }}</h4>
                                <small>Total Orders</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-shopping-bag fa-2x opacity-75"></i>
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
                                <h4 class="fw-bold mb-0">{{ $orders->where('status', 'pending')->count() }}</h4>
                                <small>Pending</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-gradient-info text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ $orders->where('status', 'approved')->count() }}</h4>
                                <small>Approved</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
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
                                <h4 class="fw-bold mb-0">{{ $orders->where('status', 'delivered')->count() }}</h4>
                                <small>Delivered</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-truck fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2 text-success"></i>
                    Recent Orders
                </h5>
            </div>
            <div class="card-body">
                @if($orders->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Orders Yet</h4>
                        <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                        <a href="{{ route('user.stockist.inventory.catalog') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i>Place Your First Order
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $order->created_at->format('M j, Y') }}<br>
                                            <span class="text-muted">{{ $order->created_at->format('g:i A') }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $order->items->count() }} item(s)<br>
                                            <span class="text-muted">{{ $order->items->sum('quantity') }} units</span>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-success">₦{{ number_format($order->grand_total, 2) }}</strong>
                                    </td>
                                    <td>
                                        @switch($order->status)
                                            @case('pending')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-check me-1"></i>Approved
                                                </span>
                                                @break
                                            @case('processing')
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-cog me-1"></i>Processing
                                                </span>
                                                @break
                                            @case('shipped')
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-shipping-fast me-1"></i>Shipped
                                                </span>
                                                @break
                                            @case('delivered')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Delivered
                                                </span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Cancelled
                                                </span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $order->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <a href="{{ route('user.stockist.inventory.order.details', $order->id) }}" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($orders->hasPages())
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                                    </small>
                                </div>
                                <div>
                                    {{ $orders->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection