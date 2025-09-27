@extends('layouts.app')

@section('title', 'Properties - Rent Tracker')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Properties</h1>
            @if(auth()->user()->akahuCredentials)
                <a href="{{ route('properties.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Property
                </a>
            @endif
        </div>

        @if($properties->count() > 0)
            <div class="row">
                @foreach($properties as $property)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ $property->name }}</h5>
                                @if($property->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt"></i> {{ $property->address }}
                                </p>

                                @if($property->tenant_name)
                                    <p class="mb-2">
                                        <i class="fas fa-user"></i> <strong>Tenant:</strong> {{ $property->tenant_name }}
                                    </p>
                                @endif

                                <p class="mb-2">
                                    <i class="fas fa-dollar-sign"></i> <strong>Rent:</strong> ${{ number_format($property->rent_amount, 2) }}
                                </p>

                                <p class="mb-3">
                                    <i class="fas fa-calendar"></i> <strong>Due:</strong> {{ $property->rent_due_day }}{{ $property->rent_due_day == 1 ? 'st' : ($property->rent_due_day == 2 ? 'nd' : ($property->rent_due_day == 3 ? 'rd' : 'th')) }} of each month
                                </p>

                                <div class="mt-auto">
                                    <div class="btn-group w-100">
                                        <a href="{{ route('properties.show', $property) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-building fa-4x text-muted mb-4"></i>
                <h3>No Properties Yet</h3>
                <p class="text-muted mb-4">Start managing your rental properties by adding your first property.</p>

                @if(auth()->user()->akahuCredentials)
                    <a href="{{ route('properties.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Add Your First Property
                    </a>
                @else
                    <div class="alert alert-info d-inline-block">
                        <p class="mb-2">You need to connect your Akahu account first before adding properties.</p>
                        <form method="POST" action="{{ route('akahu.connect') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-link"></i> Connect Akahu Account
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection