@extends('layouts.app')

@section('title', 'Add Property - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Add New Property</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('properties.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="e.g., Main Street Apartment">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tenant_name" class="form-label">Tenant Name</label>
                                <input type="text" class="form-control @error('tenant_name') is-invalid @enderror"
                                       id="tenant_name" name="tenant_name" value="{{ old('tenant_name') }}"
                                       placeholder="e.g., John Smith">
                                @error('tenant_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="3" required
                                  placeholder="Full property address">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rent_amount" class="form-label">Rent Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('rent_amount') is-invalid @enderror"
                                           id="rent_amount" name="rent_amount" value="{{ old('rent_amount') }}"
                                           step="0.01" min="0" required placeholder="0.00">
                                    @error('rent_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rent_frequency" class="form-label">Rent Frequency *</label>
                                <select class="form-control @error('rent_frequency') is-invalid @enderror"
                                        id="rent_frequency" name="rent_frequency" required>
                                    <option value="">Select frequency</option>
                                    <option value="weekly" {{ old('rent_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="fortnightly" {{ old('rent_frequency') == 'fortnightly' ? 'selected' : '' }}>Fortnightly</option>
                                    <option value="monthly" {{ old('rent_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                                @error('rent_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rent_due_day_of_week" class="form-label">Rent Due Day *</label>
                                <select class="form-control @error('rent_due_day_of_week') is-invalid @enderror"
                                        id="rent_due_day_of_week" name="rent_due_day_of_week" required>
                                    <option value="">Select day of week</option>
                                    <option value="0" {{ old('rent_due_day_of_week') == '0' ? 'selected' : '' }}>Sunday</option>
                                    <option value="1" {{ old('rent_due_day_of_week') == '1' ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ old('rent_due_day_of_week') == '2' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ old('rent_due_day_of_week') == '3' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ old('rent_due_day_of_week') == '4' ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ old('rent_due_day_of_week') == '5' ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ old('rent_due_day_of_week') == '6' ? 'selected' : '' }}>Saturday</option>
                                </select>
                                @error('rent_due_day_of_week')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bank_statement_keyword" class="form-label">Bank Statement Keyword *</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('bank_statement_keyword') is-invalid @enderror"
                                   id="bank_statement_keyword" name="bank_statement_keyword" value="{{ old('bank_statement_keyword') }}" required
                                   placeholder="e.g., RENT PAYMENT, JOHN SMITH, etc.">
                            <button type="button" class="btn btn-outline-primary" id="findTransactionBtn">
                                <i class="bi bi-search"></i> Find Transaction
                            </button>
                        </div>
                        <div class="form-text">
                            Enter a keyword or phrase that appears in bank statements to identify rent payments for this property. Or click "Find Transaction" to select from recent transactions.
                        </div>
                        @error('bank_statement_keyword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('properties.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Selection Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">Select Rent Payment Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="transactionLoadingMsg" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading transactions...</p>
                </div>
                <div id="transactionErrorMsg" class="alert alert-danger d-none"></div>
                <div id="transactionList" class="d-none">
                    <p class="text-muted">Select a transaction that represents a rent payment. The rent amount, day of week, and keyword will be auto-filled.</p>
                    <div class="list-group" id="transactionItems">
                        <!-- Transactions will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const findTransactionBtn = document.getElementById('findTransactionBtn');
    const transactionModal = new bootstrap.Modal(document.getElementById('transactionModal'));
    const rentAmountInput = document.getElementById('rent_amount');
    const rentDueDayInput = document.getElementById('rent_due_day_of_week');
    const keywordInput = document.getElementById('bank_statement_keyword');

    findTransactionBtn.addEventListener('click', function() {
        const rentAmount = rentAmountInput.value;

        if (!rentAmount || rentAmount <= 0) {
            alert('Please enter a valid rent amount first');
            rentAmountInput.focus();
            return;
        }

        // Show modal
        transactionModal.show();

        // Reset modal state
        document.getElementById('transactionLoadingMsg').classList.remove('d-none');
        document.getElementById('transactionErrorMsg').classList.add('d-none');
        document.getElementById('transactionList').classList.add('d-none');
        document.getElementById('transactionItems').innerHTML = '';

        // Fetch transactions
        fetch('{{ route('properties.transactions-for-keyword') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                rent_amount: rentAmount
            })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('transactionLoadingMsg').classList.add('d-none');

            if (data.error) {
                document.getElementById('transactionErrorMsg').textContent = data.error;
                document.getElementById('transactionErrorMsg').classList.remove('d-none');
                return;
            }

            if (!data.transactions || data.transactions.length === 0) {
                let errorMsg = 'No transactions found near $' + rentAmount + ' in the last 60 days.';
                if (data.debug) {
                    errorMsg += '\n\nDebug Info:\n';
                    errorMsg += 'Accounts found: ' + data.debug.accounts_count + '\n';
                    if (data.debug.accounts_info && data.debug.accounts_info.length > 0) {
                        errorMsg += 'Account details:\n  ' + data.debug.accounts_info.join('\n  ') + '\n';
                    }
                    errorMsg += 'Total transactions fetched: ' + data.debug.total_fetched + '\n';
                    errorMsg += 'Filtered transactions: ' + data.debug.filtered_count;
                    if (data.debug.errors && data.debug.errors.length > 0) {
                        errorMsg += '\n\nErrors:\n' + data.debug.errors.join('\n');
                    }
                }
                document.getElementById('transactionErrorMsg').innerHTML = errorMsg.replace(/\n/g, '<br>');
                document.getElementById('transactionErrorMsg').classList.remove('d-none');
                return;
            }

            // Display transactions
            const transactionItems = document.getElementById('transactionItems');
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            data.transactions.forEach(transaction => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">$${transaction.amount.toFixed(2)}</h6>
                        <small>${new Date(transaction.date).toLocaleDateString()}</small>
                    </div>
                    <p class="mb-1"><strong>${transaction.description}</strong></p>
                    ${transaction.merchant ? `<small>Merchant: ${transaction.merchant}</small><br>` : ''}
                    ${transaction.reference ? `<small>Reference: ${transaction.reference}</small><br>` : ''}
                    <small class="text-muted">Day: ${dayNames[transaction.day_of_week]}</small>
                `;

                item.addEventListener('click', function() {
                    // Auto-populate form fields
                    rentAmountInput.value = transaction.amount.toFixed(2);
                    rentDueDayInput.value = transaction.day_of_week;

                    // Extract keyword from transaction
                    let keyword = '';
                    if (transaction.merchant && transaction.merchant.trim() !== '') {
                        keyword = transaction.merchant;
                    } else if (transaction.reference && transaction.reference.trim() !== '') {
                        keyword = transaction.reference;
                    } else if (transaction.description && transaction.description.trim() !== '') {
                        // Take first meaningful word from description
                        const words = transaction.description.split(/\s+/).filter(w => w.length > 3);
                        keyword = words[0] || transaction.description;
                    }

                    keywordInput.value = keyword;

                    // Close modal
                    transactionModal.hide();
                });

                transactionItems.appendChild(item);
            });

            document.getElementById('transactionList').classList.remove('d-none');
        })
        .catch(error => {
            document.getElementById('transactionLoadingMsg').classList.add('d-none');
            document.getElementById('transactionErrorMsg').textContent = 'Error loading transactions: ' + error.message;
            document.getElementById('transactionErrorMsg').classList.remove('d-none');
        });
    });
});
</script>
@endpush