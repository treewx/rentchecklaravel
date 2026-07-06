@extends('layouts.app')

@section('title', 'Settings - Rent Tracker')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Settings</h4>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <h5 class="mb-3">Email Notification Preferences</h5>

                <form method="POST" action="{{ route('settings.email-preferences') }}">
                    @csrf

                    <div class="mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="email_notifications_enabled"
                                   name="email_notifications_enabled" value="1"
                                   {{ $user->email_notifications_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_notifications_enabled">
                                <strong>Enable Email Notifications</strong>
                            </label>
                            <div class="form-text">
                                Master switch for all email notifications. When disabled, you won't receive any rent check emails.
                            </div>
                        </div>
                    </div>

                    <div class="border-start border-3 border-primary ps-3" id="email-preferences">
                        <h6 class="mb-3">Send emails when:</h6>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input email-type-checkbox"
                                       id="email_on_rent_received" name="email_on_rent_received" value="1"
                                       {{ $user->email_on_rent_received ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_on_rent_received">
                                    <span class="text-success">✅ Rent is received on time</span>
                                </label>
                                <div class="form-text">
                                    Get notified when rent payments are successfully detected in your accounts.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input email-type-checkbox"
                                       id="email_on_rent_late" name="email_on_rent_late" value="1"
                                       {{ $user->email_on_rent_late ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_on_rent_late">
                                    <span class="text-danger">❌ Rent is late</span>
                                </label>
                                <div class="form-text">
                                    Get notified when rent payments are overdue and haven't been detected.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input email-type-checkbox"
                                       id="email_on_rent_partial" name="email_on_rent_partial" value="1"
                                       {{ $user->email_on_rent_partial ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_on_rent_partial">
                                    <span class="text-warning">⚠️ Partial rent payment is received</span>
                                </label>
                                <div class="form-text">
                                    Get notified when only partial rent payments are detected (less than the full amount).
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Profile</h5>
            </div>
            <div class="card-body">
                @if (session('status') === 'profile-information-updated')
                    <div class="alert alert-success">Profile updated successfully.</div>
                @endif

                <form method="POST" action="{{ route('user-profile-information.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control @if($errors->updateProfileInformation->has('name')) is-invalid @endif"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @if($errors->updateProfileInformation->has('name'))
                            <div class="invalid-feedback">{{ $errors->updateProfileInformation->first('name') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @if($errors->updateProfileInformation->has('email')) is-invalid @endif"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @if($errors->updateProfileInformation->has('email'))
                            <div class="invalid-feedback">{{ $errors->updateProfileInformation->first('email') }}</div>
                        @endif
                        <div class="form-text">Changing your email will require verifying the new address.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Security</h5>
            </div>
            <div class="card-body">
                @if (session('status') === 'password-updated')
                    <div class="alert alert-success">Password changed successfully.</div>
                @endif

                <h6>Change Password</h6>
                <form method="POST" action="{{ route('user-password.update') }}" class="mb-4">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
                               id="current_password" name="current_password" autocomplete="current-password" required>
                        @if($errors->updatePassword->has('current_password'))
                            <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
                               id="password" name="password" autocomplete="new-password" required>
                        @if($errors->updatePassword->has('password'))
                            <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation"
                               name="password_confirmation" autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>

                <h6>Two-Factor Authentication</h6>
                @if ($user->hasEnabledTwoFactorAuthentication())
                    <p class="text-success mb-2"><i class="fas fa-check-circle"></i> Enabled</p>
                    <p class="form-text">Two-factor authentication is required for all accounts and cannot be
                        disabled while your bank account is connected.</p>
                @else
                    <p class="text-danger mb-2"><i class="fas fa-exclamation-circle"></i> Not enabled</p>
                    <a href="{{ route('two-factor.setup') }}" class="btn btn-outline-primary">Set Up Two-Factor Authentication</a>
                @endif
            </div>
        </div>

        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Danger Zone</h5>
            </div>
            <div class="card-body">
                <p>Permanently delete your account, including all properties, rent history and your
                    bank connection. This cannot be undone.</p>

                <form method="POST" action="{{ route('settings.account.delete') }}"
                      onsubmit="return confirm('Are you sure? This permanently deletes your account and all data.');">
                    @csrf
                    @method('DELETE')

                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Confirm your password to continue</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="delete_password" name="password" autocomplete="current-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const masterSwitch = document.getElementById('email_notifications_enabled');
    const emailTypeCheckboxes = document.querySelectorAll('.email-type-checkbox');
    const emailPreferencesSection = document.getElementById('email-preferences');

    function updateEmailPreferences() {
        const isEnabled = masterSwitch.checked;

        emailTypeCheckboxes.forEach(checkbox => {
            checkbox.disabled = !isEnabled;
        });

        emailPreferencesSection.style.opacity = isEnabled ? '1' : '0.5';
    }

    masterSwitch.addEventListener('change', updateEmailPreferences);

    // Initialize on page load
    updateEmailPreferences();
});
</script>
@endsection