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