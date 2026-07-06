<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorAuthentication
{
    /**
     * Akahu accreditation requires MFA for all users, so 2FA setup is
     * enforced rather than opt-in. Can be disabled per environment
     * (e.g. local/testing) via REQUIRE_TWO_FACTOR=false.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            config('fortify.require_two_factor')
            && $user
            && !$user->hasEnabledTwoFactorAuthentication()
        ) {
            return redirect()->route('two-factor.setup')
                ->with('warning', 'Please set up two-factor authentication to continue.');
        }

        return $next($request);
    }
}
