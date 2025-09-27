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
                    <div class="card-header">
                        <h5 class="mb-0">Recent Rent Checks</h5>
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
@endsection