@extends('layouts.app')

@section('title', 'Dashboard - Rent Tracker')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard</h1>
            @if(!auth()->user()->akahuCredentials)
                <a href="{{ route('akahu.connect') }}" class="btn btn-success">
                    <i class="fas fa-link"></i> Connect Akahu Account
                </a>
            @else
                <div class="btn-group">
                    <a href="{{ route('properties.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Property
                    </a>
                    <form method="POST" action="{{ route('akahu.disconnect') }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger"
                                onclick="return confirm('Are you sure you want to disconnect your Akahu account?')">
                            <i class="fas fa-unlink"></i> Disconnect Akahu
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if(!auth()->user()->akahuCredentials)
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Getting Started</h5>
                <p>To start tracking your rent payments, you need to:</p>
                <ol>
                    <li>Connect your Akahu account by entering your App Token and User Token</li>
                    <li>Add your rental properties with rent amounts and due dates</li>
                    <li>The system will automatically check for rent payments on due dates</li>
                </ol>
                <p class="mb-0">Click "Connect Akahu Account" above to get started.</p>
            </div>
        @endif

        @if($overdueRent->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle"></i> Overdue Rent ({{ $overdueRent->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($overdueRent as $rentCheck)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $rentCheck->property->name }}</strong><br>
                                        <small class="text-muted">{{ $rentCheck->property->address }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">${{ number_format($rentCheck->expected_amount, 2) }}</div>
                                        <small class="text-danger">
                                            Due {{ $rentCheck->due_date->format('M j, Y') }}
                                            ({{ $rentCheck->due_date->diffForHumans() }})
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($upcomingRent->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Upcoming Rent ({{ $upcomingRent->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($upcomingRent as $rentCheck)
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $rentCheck->property->name }}</strong><br>
                                        <small class="text-muted">{{ $rentCheck->property->address }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">${{ number_format($rentCheck->expected_amount, 2) }}</div>
                                        <small class="text-warning">
                                            Due {{ $rentCheck->due_date->format('M j, Y') }}
                                            ({{ $rentCheck->due_date->diffForHumans() }})
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Properties Overview</h5>
                    </div>
                    <div class="card-body">
                        @if($properties->count() > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>Rent Amount</th>
                                            <th>Due Day</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($properties as $property)
                                            <tr>
                                                <td>
                                                    <strong>{{ $property->name }}</strong><br>
                                                    <small class="text-muted">{{ $property->address }}</small>
                                                </td>
                                                <td>${{ number_format($property->rent_amount, 2) }}</td>
                                                <td>{{ $property->rent_due_day }}{{ $property->rent_due_day == 1 ? 'st' : ($property->rent_due_day == 2 ? 'nd' : ($property->rent_due_day == 3 ? 'rd' : 'th')) }}</td>
                                                <td>
                                                    @if($property->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('properties.show', $property) }}"
                                                       class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <h5>No Properties Added</h5>
                                <p class="text-muted">Start by adding your first rental property</p>
                                @if(auth()->user()->akahuCredentials)
                                    <a href="{{ route('properties.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Property
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Account Status</h5>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->akahuCredentials)
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                <div>
                                    <strong>Akahu Connected</strong><br>
                                    <small class="text-muted">Bank account linked</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Connected Accounts:</strong><br>
                                @if(auth()->user()->akahuCredentials->accounts)
                                    @foreach(auth()->user()->akahuCredentials->accounts as $account)
                                        <small class="d-block text-muted">
                                            {{ $account['name'] ?? 'Account' }}
                                            ({{ $account['type'] ?? 'Unknown' }})
                                        </small>
                                    @endforeach
                                @endif
                            </div>
                        @else
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-times-circle text-danger fa-2x me-3"></i>
                                <div>
                                    <strong>Not Connected</strong><br>
                                    <small class="text-muted">Connect your bank account</small>
                                </div>
                            </div>
                        @endif

                        <hr>
                        <div class="stats">
                            <div class="stat-item mb-2">
                                <strong>Total Properties:</strong> {{ $properties->count() }}
                            </div>
                            <div class="stat-item mb-2">
                                <strong>Active Properties:</strong> {{ $properties->where('is_active', true)->count() }}
                            </div>
                            <div class="stat-item">
                                <strong>Monthly Rent:</strong> ${{ number_format($properties->where('is_active', true)->sum('rent_amount'), 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection