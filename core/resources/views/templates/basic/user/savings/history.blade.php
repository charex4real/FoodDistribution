@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="nc-wrap" id="ncWrap">
    <div class="sl-page-header d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('user.savings.index') }}" class="sl-back-btn" aria-label="Back">
            <i class="las la-arrow-left"></i>
        </a>
        <div>
            <h4 class="sl-page-title mb-0">Savings History</h4>
            <p class="sl-page-subtitle mb-0">All closed and matured savings products.</p>
        </div>
    </div>

    <div class="sl-card">
        <div class="sl-card-header">
            Past Savings Products
            <span class="sl-badge-count ms-auto">{{ $closedSavings->count() }}</span>
        </div>
        <div class="sl-card-body p-0">
            @if($closedSavings->isEmpty())
                <div class="sl-empty-state">
                    <div class="sl-empty-icon"><i class="las la-piggy-bank"></i></div>
                    <p class="sl-empty-title">No history yet</p>
                    <p class="sl-empty-sub">Closed and matured savings products will appear here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="sl-table" aria-label="Savings history">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Principal</th>
                                <th>Interest</th>
                                <th>Matured On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($closedSavings as $saving)
                            <tr>
                                <td data-label="Name" class="fw-600">{{ $saving->name }}</td>
                                <td data-label="Type">
                                    <span class="sl-type-badge sl-type-{{ $saving->type }}">{{ ucfirst($saving->type) }}</span>
                                </td>
                                <td data-label="Principal">{{ showAmount($saving->principal) }}</td>
                                <td data-label="Interest" class="text-success">+{{ showAmount($saving->interest_earned) }}</td>
                                <td data-label="Matured On">{{ $saving->matured_at ? $saving->matured_at->format('M d, Y') : '—' }}</td>
                                <td data-label="Status">
                                    <span class="sl-status sl-status-{{ $saving->status }}">{{ ucfirst($saving->status) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
