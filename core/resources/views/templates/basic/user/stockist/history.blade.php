
@extends($activeTemplate . 'layouts.master_stockist')
@section('title', 'Redemption History - Stockist Dashboard')
@section('content')
 @include($activeTemplate.'layouts.breadcrumb')

<div class="container-fluid py-4">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 fw-bold text-dark mb-1">Redemption History</h1>
                        <p class="text-muted mb-0">Track all your product redemption activities</p>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('user.stockist.dashboard') }}" class="btn btn-outline-success  rounded-pill">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-md-3 mb-2">
                <div class="card border-0 bg-gradient-primary text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ $redemptions->total() }}</h4>
                                <small>Total Redemptions</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-receipt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 mb-2">
                <div class="card border-0 bg-gradient-success text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">₦{{ number_format($redemptions->sum('total_amount'), 2) }}</h4>
                                <small>Total Value</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-coins fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 mb-2">
                <div class="card border-0 bg-gradient-info text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ $redemptions->where('created_at', '>=', now()->startOfDay())->count() }}</h4>
                                <small>Today's Redemptions</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3 mb-2">
                <div class="card border-0 bg-gradient-warning text-white shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0">{{ $redemptions->unique('invoice_id')->count() }}</h4>
                                <small>Unique Invoices</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Date Range</label>
                                <select class="form-select" id="dateFilter">
                                    <option value="all">All Time</option>
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Sort By</label>
                                <select class="form-select" id="sortFilter">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="amount_high">Amount: High to Low</option>
                                    <option value="amount_low">Amount: Low to High</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search by invoice code, customer name..." id="searchInput">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold invisible">Export</label>
                                <button class="btn btn-outline-success w-100" id="exportBtn">
                                    <i class="fas fa-download me-2"></i>Export
                                </button>
                            </div>
                        </div>

                        <!-- Custom Date Range (Hidden by Default) -->
                        <div class="row g-3 mt-2 d-none" id="customDateRange">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">From Date</label>
                                <input type="date" class="form-control" id="fromDate">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">To Date</label>
                                <input type="date" class="form-control" id="toDate">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold invisible">Apply</label>
                                <button class="btn btn-primary w-100" id="applyDateRange">
                                    <i class="fas fa-check me-1"></i>Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($redemptions->isEmpty())
            <!-- Empty State -->
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="card border-0 shadow-lg rounded-3">
                        <div class="card-body py-5">
                            <div class="empty-history-icon mb-4">
                                <i class="fas fa-receipt fa-4x text-muted opacity-25"></i>
                            </div>
                            <h3 class="text-muted mb-3">No Redemptions Yet</h3>
                            <p class="text-muted mb-4">You haven't processed any redemptions yet. They will appear here once you start redeeming customer invoices.</p>
                            <a href="{{ route('user.stockist.dashboard') }}" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-qrcode me-2"></i>Start Redeeming
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Redemptions List -->
            <div class="row">
                <div class="col-12">
                    @foreach($redemptions as $redemption)
                    <div class="card redemption-card border-0 shadow-sm rounded-3 mb-4" 
                         data-date="{{ $redemption->created_at->timestamp }}"
                         data-amount="{{ $redemption->total_amount }}"
                         data-invoice="{{ $redemption->invoice->invoice_code }}"
                         data-customer="{{ $redemption->user->name }}">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <!-- Redemption Header -->
                                <div class="col-12">
                                    <div class="p-4 border-bottom bg-light">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div class="redemption-icon me-3">
                                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fas fa-check fa-lg text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h5 class="fw-bold mb-1">Redemption #{{ $redemption->id }}</h5>
                                                        <p class="text-muted mb-0">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            {{ $redemption->created_at->format('F j, Y \a\t g:i A') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <div class="redemption-amount">
                                                    <h4 class="fw-bold text-success mb-0">₦{{ number_format($redemption->total_amount, 2) }}</h4>
                                                    <small class="text-muted">Total Amount</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3 text-end">
                                                <div class="redemption-meta">
                                                    <span class="badge bg-success fs-6 px-3 py-2 mb-2">
                                                        <i class="fas fa-receipt me-1"></i>
                                                        {{ $redemption->invoice->invoice_code }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user me-1"></i>
                                                        {{ $redemption->user->name }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Redemption Details -->
                                <div class="col-12">
                                    <div class="p-4">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="fw-semibold mb-3">
                                                    <i class="fas fa-boxes me-2 text-primary"></i>
                                                    Redeemed Items ({{ count($redemption->redeemed_items) }})
                                                </h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Product</th>
                                                                <th>Quantity</th>
                                                                <th>Price</th>
                                                                <th>Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($redemption->redeemed_items as $item)
                                                            @php
                                                                $itemArray = is_string($item) ? json_decode($item, true) : $item;
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="product-icon bg-light rounded-2 d-flex align-items-center justify-content-center me-3"
                                                                             style="width: 40px; height: 40px;">
                                                                            <i class="fas fa-box text-muted"></i>
                                                                        </div>
                                                                        <div>
                                                                            <strong class="d-block">{{ $itemArray['product_name'] ?? 'Product' }}</strong>
                                                                            <small class="text-muted">ID: {{ $itemArray['product_id'] ?? 'N/A' }}</small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-secondary fs-6">{{ $itemArray['quantity'] ?? 0 }}</span>
                                                                </td>
                                                                <td>₦{{ number_format($itemArray['price'] ?? 0, 2) }}</td>
                                                                <td>
                                                                    <strong class="text-success">
                                                                        ₦{{ number_format(($itemArray['quantity'] ?? 0) * ($itemArray['price'] ?? 0), 2) }}
                                                                    </strong>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot class="table-light">
                                                            <tr>
                                                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                                                <td class="fw-bold text-success">₦{{ number_format($redemption->total_amount, 2) }}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>

                                                @if($redemption->notes)
                                                <div class="mt-3">
                                                    <h6 class="fw-semibold mb-2">
                                                        <i class="fas fa-sticky-note me-2 text-warning"></i>
                                                        Notes
                                                    </h6>
                                                    <div class="alert alert-light border">
                                                        <p class="mb-0 text-muted">{{ $redemption->notes }}</p>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Quick Info Sidebar -->
                                            <div class="col-md-4">
                                                <div class="quick-info-card bg-light rounded-3 p-3 h-100">
                                                    <h6 class="fw-semibold mb-3">Transaction Details</h6>
                                                    
                                                    <div class="info-item d-flex justify-content-between align-items-center mb-3 p-2 bg-white rounded-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-user-circle text-primary me-2"></i>
                                                            <small class="fw-semibold">C.N: &nbsp;</small>
                                                            <small>{{ $redemption->user->fullname }}</small>
                                                        </div><br/>
                                                        
                                                    </div>
                                                    
                                                    <!-- <div class="info-item d-flex justify-content-between align-items-center mb-3 p-2 bg-white rounded-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-receipt text-success me-2"></i>
                                                            <small class="fw-semibold">Invoice Code</small>
                                                        </div>
                                                        <code class="small text-success">{{ $redemption->invoice->invoice_code }}</code>
                                                    </div> -->
                                                    
                                                    <div class="info-item d-flex justify-content-between align-items-center mb-3 p-2 bg-white rounded-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-calendar text-info me-2"></i>
                                                            <small class="fw-semibold">Date & Time</small>
                                                        </div>
                                                        <small>{{ $redemption->created_at->format('M j, Y H:i') }}</small>
                                                    </div>
                                                    
                                                    <div class="info-item d-flex justify-content-between align-items-center mb-3 p-2 bg-white rounded-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-coins text-warning me-2"></i>
                                                            <small class="fw-semibold">Amount</small>
                                                        </div>
                                                        <strong class="text-success">₦{{ number_format($redemption->total_amount, 2) }}</strong>
                                                    </div>
                                                    
                                                    <!-- <div class="info-item d-flex justify-content-between align-items-center p-2 bg-white rounded-2">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-hashtag text-secondary me-2"></i>
                                                            <small class="fw-semibold">Redemption ID</small>
                                                        </div>
                                                        <small>#{{ $redemption->id }}</small>
                                                    </div> -->

                                                    <!-- Action Buttons -->
                                                    <div class="mt-4 pt-3 border-top">
                                                        <div class="d-grid gap-2">
                                                            <button class="btn btn-outline-success btn-sm rounded-pill copy-invoice" 
                                                                    data-invoice="{{ $redemption->invoice->invoice_code }}">
                                                                <i class="fas fa-copy me-1"></i>Copy Invoice Code
                                                            </button>
                                                            <button class="btn btn-outline-info btn-sm rounded-pill view-details"
                                                                    data-redemption-id="{{ $redemption->id }}">
                                                                <i class="fas fa-eye me-1"></i>View Details
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">
                                                Showing {{ $redemptions->firstItem() }} to {{ $redemptions->lastItem() }} of {{ $redemptions->total() }} redemptions
                                            </small>
                                        </div>
                                        <div>
                                            {{ $redemptions->links() }}
                                        </div>
                                    </div>
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
<div class="modal fade" id="copySuccessModal" tabindex="-1" aria-labelledby="copySuccessModal" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h4 class="text-dark mb-3">Copied to Clipboard!</h4>
                <p class="text-muted mb-4" id="copiedInvoiceText">Invoice code has been copied successfully.</p>
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Continue</button>
            </div>
        </div>
    </div>
</div>

<!-- Redemption Details Modal -->
<div class="modal fade" id="redemptionDetailsModal" tabindex="-1" aria-labelledby="redemptionDetailsModal" aria-hidden="true">   

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Redemption Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="redemptionDetailsContent">
                <!-- Content will be loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>
@endpush


@push('script')
<script>
$(document).ready(function() {
    // Copy invoice code functionality
    $('.copy-invoice').on('click', function() {
        const invoiceCode = $(this).data('invoice');
        
        navigator.clipboard.writeText(invoiceCode).then(function() {
            $('#copiedInvoiceText').html(
                `Invoice code <strong>${invoiceCode}</strong> has been copied to clipboard.`
            );
            $('#copySuccessModal').modal('show');
        }).catch(function(err) {
            alert('Failed to copy invoice code: ' + err);
        });
    });

    // Date filter functionality
    $('#dateFilter').on('change', function() {
        const value = $(this).val();
        
        if (value === 'custom') {
            $('#customDateRange').removeClass('d-none');
        } else {
            $('#customDateRange').addClass('d-none');
            filterRedemptions();
        }
    });

    // Apply custom date range
    $('#applyDateRange').on('click', function() {
        filterRedemptions();
    });

    // Sort and search functionality
    $('#sortFilter, #searchInput').on('change input', function() {
        filterRedemptions();
    });

    // Export functionality
    $('#exportBtn').on('click', function() {
        exportToCSV();
    });

    // View redemption details
    $('.view-details').on('click', function() {
        const redemptionId = $(this).data('redemption-id');
        showRedemptionDetails(redemptionId);
    });

    function filterRedemptions() {
        const dateFilter = $('#dateFilter').val();
        const sortFilter = $('#sortFilter').val();
        const searchTerm = $('#searchInput').val().toLowerCase();
        
        $('.redemption-card').each(function() {
            const $card = $(this);
            const date = parseInt($card.data('date'));
            const amount = parseFloat($card.data('amount'));
            const invoice = $card.data('invoice').toLowerCase();
            const customer = $card.data('customer').toLowerCase();
            
            // Date filter
            let dateMatch = true;
            if (dateFilter !== 'all') {
                const now = new Date();
                const cardDate = new Date(date * 1000);
                
                switch(dateFilter) {
                    case 'today':
                        dateMatch = cardDate.toDateString() === now.toDateString();
                        break;
                    case 'week':
                        const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
                        dateMatch = cardDate >= startOfWeek;
                        break;
                    case 'month':
                        const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
                        dateMatch = cardDate >= startOfMonth;
                        break;
                    case 'custom':
                        const fromDate = $('#fromDate').val();
                        const toDate = $('#toDate').val();
                        if (fromDate && toDate) {
                            dateMatch = cardDate >= new Date(fromDate) && cardDate <= new Date(toDate);
                        }
                        break;
                }
            }
            
            // Search filter
            let searchMatch = invoice.includes(searchTerm) || 
                            customer.includes(searchTerm) || 
                            searchTerm === '';
            
            // Show/hide based on filters
            if (dateMatch && searchMatch) {
                $card.show();
            } else {
                $card.hide();
            }
        });
        
        // Sort redemptions
        sortRedemptions(sortFilter);
    }

    function sortRedemptions(sortBy) {
        const $container = $('.redemption-card').parent();
        const $redemptions = $('.redemption-card').get();
        
        $redemptions.sort(function(a, b) {
            const $a = $(a);
            const $b = $(b);
            
            switch(sortBy) {
                case 'newest':
                    return parseInt($b.data('date')) - parseInt($a.data('date'));
                case 'oldest':
                    return parseInt($a.data('date')) - parseInt($b.data('date'));
                case 'amount_high':
                    return parseFloat($b.data('amount')) - parseFloat($a.data('amount'));
                case 'amount_low':
                    return parseFloat($a.data('amount')) - parseFloat($b.data('amount'));
                default:
                    return 0;
            }
        });
        
        $.each($redemptions, function(i, redemption) {
            $container.append(redemption);
        });
    }

    function exportToCSV() {
        // Simple CSV export implementation
        let csv = 'Redemption ID,Invoice Code,Customer,Amount,Date,Items\n';
        
        $('.redemption-card:visible').each(function() {
            const $card = $(this);
            const redemptionId = $card.find('.view-details').data('redemption-id');
            const invoiceCode = $card.data('invoice');
            const customer = $card.data('customer');
            const amount = $card.data('amount');
            const date = new Date(parseInt($card.data('date')) * 1000).toLocaleDateString();
            
            csv += `"${redemptionId}","${invoiceCode}","${customer}","${amount}","${date}","${$card.find('tbody tr').length} items"\n`;
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('hidden', '');
        a.setAttribute('href', url);
        a.setAttribute('download', `redemptions-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function showRedemptionDetails(redemptionId) {
        // For now, just show a simple message
        // In a real application, you might fetch detailed data via AJAX
        $('#redemptionDetailsContent').html(`
            <div class="text-center py-4">
                <i class="fas fa-receipt fa-3x text-primary mb-3"></i>
                <h5>Redemption #${redemptionId}</h5>
                <p class="text-muted">Detailed view for redemption ${redemptionId}</p>
            </div>
        `);
        $('#redemptionDetailsModal').modal('show');
    }

    // Add hover effects
    $('.redemption-card').hover(
        function() {
            $(this).css('transform', 'translateY(-3px)');
            $(this).css('box-shadow', '0 12px 35px rgba(0, 0, 0, 0.1)');
        },
        function() {
            $(this).css('transform', 'translateY(0)');
            $(this).css('box-shadow', '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)');
        }
    );

    // Initialize with current date for date inputs
    const today = new Date().toISOString().split('T')[0];
    $('#fromDate').val(today);
    $('#toDate').val(today);
});
</script>
@endpush