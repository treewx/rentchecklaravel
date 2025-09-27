@extends('layouts.app')

@section('title', 'Welcome - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center">
                <h1 class="card-title">Welcome to Rent Tracker</h1>
                <p class="card-text lead">
                    Track your rental property payments automatically with Akahu integration
                </p>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="feature-box">
                            <i class="fas fa-link fa-3x text-primary mb-3"></i>
                            <h5>Connect Your Bank</h5>
                            <p>Securely connect your bank account through Akahu</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <i class="fas fa-building fa-3x text-success mb-3"></i>
                            <h5>Manage Properties</h5>
                            <p>Add your rental properties and set rent due dates</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                            <h5>Auto-Check Payments</h5>
                            <p>Automatically verify rent payments on due dates</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-2">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">Get Started</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.feature-box {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>
@endsection