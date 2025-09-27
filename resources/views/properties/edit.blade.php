@extends('layouts.app')

@section('title', 'Edit ' . $property->name . ' - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Property: {{ $property->name }}</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('properties.update', $property) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $property->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tenant_name" class="form-label">Tenant Name</label>
                                <input type="text" class="form-control @error('tenant_name') is-invalid @enderror"
                                       id="tenant_name" name="tenant_name" value="{{ old('tenant_name', $property->tenant_name) }}">
                                @error('tenant_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="3" required>{{ old('address', $property->address) }}</textarea>
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
                                           id="rent_amount" name="rent_amount" value="{{ old('rent_amount', $property->rent_amount) }}"
                                           step="0.01" min="0" required>
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
                                    <option value="weekly" {{ old('rent_frequency', $property->rent_frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="fortnightly" {{ old('rent_frequency', $property->rent_frequency) == 'fortnightly' ? 'selected' : '' }}>Fortnightly</option>
                                    <option value="monthly" {{ old('rent_frequency', $property->rent_frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
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
                                    <option value="0" {{ old('rent_due_day_of_week', $property->rent_due_day_of_week) == '0' ? 'selected' : '' }}>Sunday</option>
                                    <option value="1" {{ old('rent_due_day_of_week', $property->rent_due_day_of_week) == '1' ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ old('rent_due_day_of_week', $property->rent_due_day_of_week) == '2' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ old('rent_due_day_of_week', $property->rent_due_day_of_week) == '3' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ old('rent_due_day_of_week', $property->rent_due_day_of_week) == '4' ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ old('rent_due_day_of_week', $property->rent_due_day_of_week) == '5' ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ old('rent_due_day_of_week', $property->rent_due_day_of_week) == '6' ? 'selected' : '' }}>Saturday</option>
                                </select>
                                @error('rent_due_day_of_week')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bank_statement_keyword" class="form-label">Bank Statement Keyword *</label>
                        <input type="text" class="form-control @error('bank_statement_keyword') is-invalid @enderror"
                               id="bank_statement_keyword" name="bank_statement_keyword" value="{{ old('bank_statement_keyword', $property->bank_statement_keyword) }}" required
                               placeholder="e.g., RENT PAYMENT, JOHN SMITH, etc.">
                        <div class="form-text">
                            Enter a keyword or phrase that appears in bank statements to identify rent payments for this property.
                        </div>
                        @error('bank_statement_keyword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $property->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Property is active (rent checking enabled)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('properties.show', $property) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                        <div>
                            <form method="POST" action="{{ route('properties.destroy', $property) }}" class="d-inline me-2"
                                  onsubmit="return confirm('Are you sure you want to delete this property? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i> Delete Property
                                </button>
                            </form>
                            <button type="submit" class="btn btn-primary">Update Property</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection