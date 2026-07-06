@extends('layouts.app')

@section('title', 'Set Up Two-Factor Authentication - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-shield-alt"></i> Two-Factor Authentication</h4>
            </div>
            <div class="card-body">
                @if (session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                @php($user = auth()->user())

                @if ($user->hasEnabledTwoFactorAuthentication())
                    {{-- Enabled and confirmed --}}
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Two-factor authentication is enabled on your account.
                    </div>

                    @if (session('status') === 'two-factor-authentication-confirmed')
                        <p class="fw-bold">Save these recovery codes in a safe place. Each can be used once to
                            access your account if you lose your authenticator device.</p>
                        <pre class="bg-light border rounded p-3">@foreach ($user->recoveryCodes() as $code){{ $code }}
@endforeach</pre>
                    @endif

                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Continue to Dashboard</a>

                @elseif ($user->two_factor_secret)
                    {{-- Secret generated, awaiting confirmation --}}
                    <p>Scan the QR code below with your authenticator app (Google Authenticator, 1Password,
                        Authy, etc.), then enter the 6-digit code to confirm.</p>

                    <div class="text-center my-3">
                        {!! $user->twoFactorQrCodeSvg() !!}
                    </div>

                    <p class="text-muted small text-center">
                        Can't scan? Enter this key manually: <code>{{ decrypt($user->two_factor_secret) }}</code>
                    </p>

                    <form method="POST" action="{{ route('two-factor.confirm') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="code" class="form-label">Authentication Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   id="code" name="code" inputmode="numeric" autocomplete="one-time-code"
                                   required autofocus>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Confirm &amp; Enable</button>
                    </form>

                @else
                    {{-- Not yet started --}}
                    <p>Two-factor authentication adds an extra layer of security to your account.
                        Because Rent Tracker connects to your bank data, it is required for all accounts.</p>
                    <p>You'll need an authenticator app such as Google Authenticator, Microsoft
                        Authenticator, Authy or 1Password.</p>

                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-qrcode"></i> Begin Setup
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
