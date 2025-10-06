@extends('layouts.app')

@section('title', $property->name . ' - Rent Tracker')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>{{ $property->name }}</h1>
                <p class="text-muted mb-0">
                    <i class="fas fa-map-marker-alt"></i> {{ $property->address }}
                </p>
            </div>
            <div class="btn-group">
                <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('properties.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Properties
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Property Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong>Monthly Rent:</strong>
                            </div>
                            <div class="col-sm-6">
                                ${{ number_format($property->rent_amount, 2) }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong>Due Date:</strong>
                            </div>
                            <div class="col-sm-6">
                                {{ $property->rent_due_day }}{{ $property->rent_due_day == 1 ? 'st' : ($property->rent_due_day == 2 ? 'nd' : ($property->rent_due_day == 3 ? 'rd' : 'th')) }} of each month
                            </div>
                        </div>

                        @if($property->tenant_name)
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong>Tenant:</strong>
                                </div>
                                <div class="col-sm-6">
                                    {{ $property->tenant_name }}
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong>Status:</strong>
                            </div>
                            <div class="col-sm-6">
                                @if($property->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <strong>Next Due:</strong>
                            </div>
                            <div class="col-sm-6">
                                {{ $property->next_rent_due_date->format('M j, Y') }}
                                <br>
                                <small class="text-muted">
                                    ({{ $property->next_rent_due_date->diffForHumans() }})
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 d-inline">Recent Rent Checks</h5>
                            <span class="ms-3">
                                Balance:
                                <strong class="{{ $property->hasOutstandingBalance() ? 'text-danger' : 'text-success' }}">
                                    {{ $property->hasOutstandingBalance() ? '-' : '' }}${{ $property->formatted_balance }}
                                </strong>
                            </span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                            <i class="fas fa-plus"></i> Add Payment
                        </button>
                    </div>
                    <div class="card-body">
                        @if($rentChecks->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Due Date</th>
                                            <th>Expected</th>
                                            <th>Received</th>
                                            <th>Status</th>
                                            <th>Checked</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rentChecks as $rentCheck)
                                            <tr>
                                                <td>
                                                    {{ $rentCheck->due_date->format('M j, Y') }}
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $rentCheck->due_date->diffForHumans() }}
                                                    </small>
                                                </td>
                                                <td>${{ number_format($rentCheck->expected_amount, 2) }}</td>
                                                <td>
                                                    @if($rentCheck->received_amount)
                                                        ${{ number_format($rentCheck->received_amount, 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @switch($rentCheck->status)
                                                        @case('received')
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check"></i> Received
                                                            </span>
                                                            @break
                                                        @case('partial')
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-exclamation-triangle"></i> Partial
                                                            </span>
                                                            @break
                                                        @case('late')
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-clock"></i> Late
                                                            </span>
                                                            @break
                                                        @case('pending')
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-clock"></i> Pending
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td>
                                                    @if($rentCheck->checked_at)
                                                        <small class="text-muted">
                                                            {{ $rentCheck->checked_at->format('M j, g:i A') }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted">Not checked</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $rentChecks->links() }}
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5>No Rent Checks Yet</h5>
                                <p class="text-muted">Rent checks will appear here once the system starts monitoring this property.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalLabel">Add Manual Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="paymentErrorAlert"></div>
                    <div class="alert alert-success d-none" id="paymentSuccessAlert"></div>

                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="payment_amount" name="amount" step="0.01" min="0.01" required>
                        </div>
                        <div class="invalid-feedback" id="amount_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_date" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                        <div class="invalid-feedback" id="transaction_date_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="rent_check_id" class="form-label">Apply to Rent Check (Optional)</label>
                        <select class="form-select" id="rent_check_id" name="rent_check_id">
                            <option value="">-- Select a rent check --</option>
                            @foreach($rentChecks as $rentCheck)
                                @if($rentCheck->status !== 'received')
                                    <option value="{{ $rentCheck->id }}">
                                        {{ $rentCheck->due_date->format('M j, Y') }} -
                                        ${{ number_format($rentCheck->expected_amount, 2) }}
                                        ({{ ucfirst($rentCheck->status) }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="rent_check_id_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_description" class="form-label">Description / Note (Optional)</label>
                        <textarea class="form-control" id="payment_description" name="description" rows="3" maxlength="500"></textarea>
                        <div class="invalid-feedback" id="description_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitPaymentBtn">
                        <i class="fas fa-save"></i> Add Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('addPaymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = document.getElementById('submitPaymentBtn');
    const errorAlert = document.getElementById('paymentErrorAlert');
    const successAlert = document.getElementById('paymentSuccessAlert');

    // Clear previous errors
    errorAlert.classList.add('d-none');
    successAlert.classList.add('d-none');
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

    try {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const response = await fetch('{{ route("properties.transactions.store", $property) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            successAlert.textContent = result.message;
            successAlert.classList.remove('d-none');

            // Reset form
            form.reset();
            document.getElementById('payment_date').value = '{{ date("Y-m-d") }}';

            // Reload page after 1.5 seconds to show updated balance and rent checks
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // Handle validation errors
            if (result.errors) {
                Object.keys(result.errors).forEach(field => {
                    const input = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
                    const errorDiv = document.getElementById(`${field}_error`);

                    if (input) {
                        input.classList.add('is-invalid');
                    }
                    if (errorDiv) {
                        errorDiv.textContent = result.errors[field][0];
                    }
                });
            }

            errorAlert.textContent = result.message || 'Failed to add payment. Please check the form.';
            errorAlert.classList.remove('d-none');
        }
    } catch (error) {
        console.error('Error:', error);
        errorAlert.textContent = 'An error occurred while adding the payment.';
        errorAlert.classList.remove('d-none');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Payment';
    }
});
</script>
@endpush
@endsection