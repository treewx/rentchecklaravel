@extends('layouts.app')

@section('title', 'Connect Akahu - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-link text-primary"></i> Connect Akahu Account</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Manual Token Entry</h6>
                    <p class="mb-0">
                        Enter your Akahu App Token and User Token below. You can get these tokens from your
                        <a href="https://developers.akahu.nz" target="_blank">Akahu Developer Dashboard</a>.
                    </p>
                </div>

                <form method="POST" action="{{ route('akahu.store-tokens') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="app_token" class="form-label">
                            <i class="fas fa-key"></i> App Token
                        </label>
                        <textarea class="form-control @error('app_token') is-invalid @enderror"
                               id="app_token" name="app_token" rows="3"
                               placeholder="app_token_xxxxxxxx..." required>{{ old('app_token') }}</textarea>
                        @error('app_token')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Your Akahu Application Token (starts with "app_token_")
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="user_token" class="form-label">
                            <i class="fas fa-user-lock"></i> User Token
                        </label>
                        <textarea class="form-control @error('user_token') is-invalid @enderror"
                               id="user_token" name="user_token" rows="3"
                               placeholder="user_token_xxxxxxxx..." required>{{ old('user_token') }}</textarea>
                        @error('user_token')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Your Akahu User Token (starts with "user_token_")
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Security Note</h6>
                        <p class="mb-0">
                            These tokens will be stored securely and used to access your bank account data through Akahu.
                            Never share these tokens with anyone else.
                        </p>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Tokens
                        </button>
                    </div>
                </form>

                <div class="mt-4">
                    <h6>Need help getting your tokens?</h6>
                    <ol class="small">
                        <li>Go to your <a href="https://developers.akahu.nz" target="_blank">Akahu Developer Dashboard</a></li>
                        <li>Create an application if you haven't already</li>
                        <li>Copy your App Token from the application settings</li>
                        <li>Generate a User Token for your account</li>
                        <li>Paste both tokens in the fields above</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection