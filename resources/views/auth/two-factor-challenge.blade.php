@extends('layouts.app')

@section('title', 'Two-Factor Authentication - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Two-Factor Authentication</h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('two-factor.login.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="code" class="form-label">Authentication Code</label>
                        <input type="text" class="form-control" id="code" name="code"
                               inputmode="numeric" autocomplete="one-time-code" autofocus
                               placeholder="Enter the 6-digit code from your authenticator app">
                    </div>

                    <div class="mb-3">
                        <label for="recovery_code" class="form-label">Or use a Recovery Code</label>
                        <input type="text" class="form-control" id="recovery_code" name="recovery_code"
                               autocomplete="off" placeholder="Enter one of your recovery codes">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Verify</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
