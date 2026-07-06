@extends('layouts.app')

@section('title', 'Verify Email - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Verify Your Email Address</h4>
            </div>
            <div class="card-body">
                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">
                        A new verification link has been sent to your email address.
                    </div>
                @endif

                <p>
                    Thanks for signing up! Before getting started, please verify your email address
                    by clicking the link we just emailed to you. If you didn't receive the email,
                    we'll gladly send you another.
                </p>

                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Log Out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
