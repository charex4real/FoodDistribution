{{-- resources/views/stockist/profile.blade.php --}}

@extends($activeTemplate . 'layouts.master_stockist')
@section('title', 'Stockist Profile - Manage Your Business Information')
@section('content')
@include($activeTemplate.'layouts.breadcrumb')
<div class="container-fluid py-4">
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                       
                        <p class="text-muted mb-0">Manage your business information and locations</p>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('user.stockist.dashboard') }}" class="btn btn-outline-success rounded-pill">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Sidebar - Navigation -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 100px;">
                    <div class="card-header bg-success text-white rounded-top">
                        <h6 class="mb-0 text-center text-white">Profile Sections</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a class="list-group-item list-group-item-action active" href="#basic-info" data-bs-toggle="tab">
                                <i class="fas fa-building me-2"></i>Basic Info
                            </a>
                            <a class="list-group-item list-group-item-action" href="#location" data-bs-toggle="tab">
                                <i class="fas fa-map-marker-alt me-2"></i>Location
                            </a>
                            <a class="list-group-item list-group-item-action" href="#opening-hours" data-bs-toggle="tab">
                                <i class="fas fa-clock me-2"></i>Opening Hours
                            </a>
                            <a class="list-group-item list-group-item-action" href="#services" data-bs-toggle="tab">
                                <i class="fas fa-concierge-bell me-2"></i>Services
                            </a>
                            <a class="list-group-item list-group-item-action" href="#preview" data-bs-toggle="tab">
                                <i class="fas fa-eye me-2"></i>Preview
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="card border-0 shadow-sm rounded-3 mt-4">
                    <div class="card-body text-center">
                        <div class="status-icon mb-3">
                            @if($stockist->is_active)
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-check fa-lg text-white"></i>
                                </div>
                                <h6 class="text-success mt-2 mb-1">Verified Stockist</h6>
                            @else
                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mx-auto"
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-clock fa-lg text-white"></i>
                                </div>
                                <h6 class="text-warning mt-2 mb-1">Pending Verification</h6>
                            @endif
                        </div>
                        <small class="text-muted">
                            {{ $stockist->is_active ? 'Your account is verified and active' : 'Your account is pending admin verification' }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Right Content - Forms -->
            <div class="col-lg-9">
                <div class="tab-content">
                    <!-- Basic Information Tab -->
                    <div class="tab-pane fade show active" id="basic-info">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-building me-2 text-success"></i>
                                    Basic Business Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="basicInfoForm">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Name *</label>
                                            <input type="text" class="form-control" name="business_name" 
                                                   value="{{ $stockist->business_name }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Registration Number</label>
                                            <input type="text" class="form-control" name="business_registration_number"
                                                   value="{{ $stockist->business_registration_number }}" 
                                                   placeholder="Optional">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Email *</label>
                                            <input type="email" class="form-control" name="business_email"
                                                   value="{{ $stockist->business_email }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Phone *</label>
                                            <input type="text" class="form-control" name="business_phone"
                                                   value="{{ $stockist->business_phone }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Website</label>
                                            <input type="url" class="form-control" name="website"
                                                   value="{{ $stockist->website }}" placeholder="https://example.com">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Business Description</label>
                                            <textarea class="form-control" name="business_description" rows="4"
                                                      placeholder="Describe your business...">{{ $stockist->business_description }}</textarea>
                                            <small class="text-muted">Tell customers about your business (max 1000 characters)</small>
                                        </div>
                                    </div>
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Location Tab -->
                    <div class="tab-pane fade" id="location">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                    Business Location
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="locationForm">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Address Line 1 *</label>
                                            <input type="text" class="form-control" name="address_line_1"
                                                   value="{{ $stockist->primaryLocation->address_line_1 ?? '' }}" 
                                                   placeholder="Street address, P.O. box" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Address Line 2</label>
                                            <input type="text" class="form-control" name="address_line_2"
                                                   value="{{ $stockist->primaryLocation->address_line_2 ?? '' }}" 
                                                   placeholder="Apartment, suite, unit, building, floor, etc.">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">City *</label>
                                            <input type="text" class="form-control" name="city"
                                                   value="{{ $stockist->primaryLocation->city ?? '' }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">State *</label>
                                            <input type="text" class="form-control" name="state"
                                                   value="{{ $stockist->primaryLocation->state ?? '' }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Postal Code</label>
                                            <input type="text" class="form-control" name="postal_code"
                                                   value="{{ $stockist->primaryLocation->postal_code ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Country *</label>
                                            <select class="form-select" name="country" required>
                                                <option value="Nigeria" {{ ($stockist->primaryLocation->country ?? 'Nigeria') == 'Nigeria' ? 'selected' : '' }}>Nigeria</option>
                                                <option value="Ghana" {{ ($stockist->primaryLocation->country ?? '') == 'Ghana' ? 'selected' : '' }}>Ghana</option>
                                                <option value="Kenya" {{ ($stockist->primaryLocation->country ?? '') == 'Kenya' ? 'selected' : '' }}>Kenya</option>
                                                <option value="South Africa" {{ ($stockist->primaryLocation->country ?? '') == 'South Africa' ? 'selected' : '' }}>South Africa</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Location Phone</label>
                                            <input type="text" class="form-control" name="phone"
                                                   value="{{ $stockist->primaryLocation->phone ?? '' }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Location Email</label>
                                            <input type="email" class="form-control" name="email"
                                                   value="{{ $stockist->primaryLocation->email ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Save Location
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Opening Hours Tab -->
                    <div class="tab-pane fade" id="opening-hours">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock me-2 text-success"></i>
                                    Opening Hours
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="openingHoursForm">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Day</th>
                                                    <th>Open</th>
                                                    <th>Close</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="openingHoursBody">
                                                <!-- Opening hours will be populated by JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Save Hours
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Services Tab -->
                    <div class="tab-pane fade" id="services">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-concierge-bell me-2 text-success"></i>
                                    Services Offered
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="servicesForm">
                                    @csrf
                                    <div id="servicesContainer">
                                        <!-- Services will be populated by JavaScript -->
                                    </div>
                                    <div class="d-grid gap-2 mt-3">
                                        <button type="button" class="btn btn-outline-success rounded-pill" id="addServiceBtn">
                                            <i class="fas fa-plus me-2"></i>Add Another Service
                                        </button>
                                    </div>
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Save Services
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Tab -->
                    <div class="tab-pane fade" id="preview">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-eye me-2 text-success"></i>
                                    Profile Preview
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="profilePreview">
                                    <!-- Preview will be loaded by JavaScript -->
                                </div>
                            </div>
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
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="reStockmodal" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h4 class="text-dark mb-3" id="successTitle">Success!</h4>
                <p class="text-muted mb-4" id="successMessage">Your changes have been saved successfully.</p>
                <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">Continue</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('style')
<style>
.bg-success {
    background:  #00715D !important;
}

.sticky-top {
    position: sticky;
    z-index: 1020;
}

.list-group-item.active {
    background: #00715d;
    border-color: ##00715d !important;
}

.tab-pane {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.service-item {
    border: 1px solid #00715d !important;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: #00715d !important;
}

.service-item:hover {
    border-color: #bef4ea;
}

.remove-service {
   
    transition: color 0.2s ease;
}

.remove-service:hover {
    color: #fffff !important;
}

.table th {
    font-weight: 600;
    background: #f8f9fa;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.form-check-input:checked {
    background-color: #00715d !important;
    border-color: #00715d !important;
}
</style>
@endpush

@push('script')
<script>
$(document).ready(function() {
    // Initialize opening hours and services
    loadOpeningHours();
    loadServices();

    // Basic Info Form
    $('#basicInfoForm').on('submit', function(e) {
        e.preventDefault();
        submitForm('{{ route("user.stockist.profile.basic-info") }}', $(this).serialize(), 'Basic information');
    });

    // Location Form
    $('#locationForm').on('submit', function(e) {
        e.preventDefault();
        submitForm('{{ route("user.stockist.profile.location") }}', $(this).serialize(), 'Location information');
    });

    // Opening Hours Form
    $('#openingHoursForm').on('submit', function(e) {
        e.preventDefault();
        const openingHours = {};
        $('#openingHoursBody tr').each(function() {
            const day = $(this).data('day');
            const open = $(this).find('.open-time').val();
            const close = $(this).find('.close-time').val();
            openingHours[day] = { open, close };
        });
        
        submitForm('{{ route("user.stockist.profile.opening-hours") }}', { opening_hours: openingHours }, 'Opening hours');
    });

    // Services Form
    $('#servicesForm').on('submit', function(e) {
        e.preventDefault();
        //var services = [];
        

        // $('.service-item').each(function() {

        //     const serviceName = $(this).find('.service-name').val();
        //     const description = $(this).find('.service-description').val();
        //     //alert(serviceName);

        //     if (serviceName) {
        //         services.push({ service_name: serviceName, description: description });
        //     }
        // });

        
        submitForm('{{ route("user.stockist.profile.services") }}', $(this).serialize(), 'Services');
    });

    // Add Service Button
    $('#addServiceBtn').on('click', function() {
        addServiceField('', '');
    });

    // Tab change - load preview when preview tab is selected
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if (e.target.getAttribute('href') === '#preview') {
            loadProfilePreview();
        }
    });

    function submitForm(url, data, title) {
        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#successTitle').text('Success!');
                    $('#successMessage').text(response.message);
                    $('#successModal').modal('show');
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                showError('An error occurred. Please try again.');
            }
        });
    }
    function submitForm1(url, data1, title) {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        alert(csrfToken);

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                data:data1,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    $('#successTitle').text('Success!');
                    $('#successMessage').text(response.message);
                    $('#successModal').modal('show');
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                showError('An error occurred. Please try again.');
            }
        });
    }


    function loadOpeningHours() {
        $.get('{{ route("user.stockist.profile.data") }}', function(response) {
            if (response.success) {
                const openingHours = response.primary_location?.opening_hours || {};
                const days = [
                    { key: 'monday', label: 'Monday' },
                    { key: 'tuesday', label: 'Tuesday' },
                    { key: 'wednesday', label: 'Wednesday' },
                    { key: 'thursday', label: 'Thursday' },
                    { key: 'friday', label: 'Friday' },
                    { key: 'saturday', label: 'Saturday' },
                    { key: 'sunday', label: 'Sunday' }
                ];

                let html = '';
                days.forEach(day => {
                    const hours = openingHours[day.key] || { open: '08:00', close: '17:00' };
                    const isClosed = !hours.open || !hours.close;
                    
                    html += `
                        <tr data-day="${day.key}">
                            <td class="fw-semibold">${day.label}</td>
                            <td>
                                <input type="time" class="form-control form-control-sm open-time" 

                                       value="${hours.open}" ${isClosed ? 'disabled' : ''}>
                            </td>
                            <td>
                                <input type="time" class="form-control form-control-sm close-time" 
                                       value="${hours.close}" ${isClosed ? 'disabled' : ''}>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input hours-toggle" type="checkbox" 
                                           ${isClosed ? '' : 'checked'}>
                                    <label class="form-check-label small">${isClosed ? 'Closed' : 'Open'}</label>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                $('#openingHoursBody').html(html);

                // Add toggle functionality
                $('.hours-toggle').on('change', function() {
                    const $row = $(this).closest('tr');
                    const isOpen = $(this).is(':checked');
                    $row.find('.open-time, .close-time').prop('disabled', !isOpen);
                    $row.find('.form-check-label').text(isOpen ? 'Open' : 'Closed');
                });
            }
        });
    }

    function loadServices() {
        $.get('{{ route("user.stockist.profile.data") }}', function(response) {
            if (response.success) {
                $('#servicesContainer').empty();
                if (response.services.length > 0) {
                    response.services.forEach(service => {
                        addServiceField(service.service_name, service.description);
                    });
                } else {
                    addServiceField('Product Redemption', 'Redeem customer products using invoice codes');
                    addServiceField('Customer Support', 'Provide support and assistance to customers');
                }
            }
        });
    }

    function addServiceField(name = '', description = '') {
        const serviceId = Date.now();
        const html = `
            <div class="service-item" data-id="${serviceId}">
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold text-white">Service Name</label>
                        <input type="text" class="form-control service-name"  name="serviceName[]"
                               value="${name}" placeholder="e.g., Product Redemption">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-white">Description</label>
                        <textarea  class="form-control service-description"  name="description[]"
                               >${description}
                        </textarea>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold invisible">Remove</label>
                        <button type="button" class="btn btn-outline-success btn-sm w-100 remove-service text-white" 
                                onclick="$(this).closest('.service-item').remove()">
                            <i class="fas fa-times"></i>X
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#servicesContainer').append(html);
    }

    function loadProfilePreview() {
        $.get('{{ route("user.stockist.profile.data") }}', function(response) {
            if (response.success) {
                const stockist = response.stockist;
                const location = response.primary_location;
                const services = response.services;

                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-success mb-3">Business Information</h6>
                            <div class="mb-3">
                                <strong>Business Name:</strong> ${stockist.business_name}<br>
                                <strong>Email:</strong> ${stockist.business_email}<br>
                                <strong>Phone:</strong> ${stockist.business_phone}<br>
                                ${stockist.website ? `<strong>Website:</strong> <a href="${stockist.website}" target="_blank">${stockist.website}</a><br>` : ''}
                                ${stockist.business_registration_number ? `<strong>Registration:</strong> ${stockist.business_registration_number}<br>` : ''}
                            </div>
                            ${stockist.business_description ? `
                            <div class="mb-3">
                                <strong>Description:</strong>
                                <p class="text-muted mt-1">${stockist.business_description}</p>
                            </div>` : ''}
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-semibold text-success mb-3">Location</h6>
                `;

                if (location) {
                    html += `
                        <div class="mb-3">
                            <strong>Address:</strong><br>
                            ${location.address_line_1}<br>
                            ${location.address_line_2 ? location.address_line_2 + '<br>' : ''}
                            ${location.city}, ${location.state}<br>
                            ${location.country}${location.postal_code ? ' - ' + location.postal_code : ''}
                        </div>
                        ${location.phone ? `<div><strong>Phone:</strong> ${location.phone}</div>` : ''}
                        ${location.email ? `<div><strong>Email:</strong> ${location.email}</div>` : ''}
                    `;
                } else {
                    html += `<div class="text-muted">No location information added yet.</div>`;
                }

                html += `</div></div>`;

                if (services.length > 0) {
                    html += `
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="fw-semibold text-success mb-3">Services Offered</h6>
                                <div class="d-flex flex-wrap gap-2">
                    `;
                    services.forEach(service => {
                        html += `<span class="badge bg-success text-white">${service.service_name}</span>`;
                    });
                    html += `</div></div></div>`;
                }

                $('#profilePreview').html(html);
            }
        });
    }

    function showError(message) {
        alert('Error: ' + message);
    }
});
</script>
@endpush