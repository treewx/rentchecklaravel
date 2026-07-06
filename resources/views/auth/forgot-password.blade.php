@extends('layouts.app')

@section('title', 'Forgot Password - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Forgot Password</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Email Password Reset Link</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p><a href="{{ route('login') }}">Back to login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
