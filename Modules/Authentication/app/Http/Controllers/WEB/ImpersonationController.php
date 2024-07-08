<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Authentication\Models\ImpersonationToken;
use RealRashid\SweetAlert\Facades\Alert;

/**
 * The consuming half of "Login as institute" (see API\ImpersonationController
 * for the minting half). Runs entirely in this app's `web` (session) guard.
 *
 * Why this is not an authentication bypass:
 *   - `consume()` accepts only a single-use, 2-minute token that was minted by
 *     an authenticated SaaS admin. It logs the browser in as the institute's
 *     own admin user via the normal `Auth::login()` path, so every downstream
 *     guard, role, permission and — crucially — the `get_institute_id()` tenant
 *     scope resolve exactly as they would for a real login by that user. There
 *     is no separate, weaker code path: the impersonated session IS a real
 *     session for a real institute user, which is what keeps tenant isolation
 *     intact and prevents reaching another institute's data.
 *   - The SaaS admin's own session lives in the separate panel application and
 *     is never touched here, so "Back to SaaS Admin" simply ends this tenant
 *     session and returns the browser to the still-authenticated panel.
 */
class ImpersonationController extends Controller
{
    public function consume(Request $request, string $token): RedirectResponse
    {
        $impersonationToken = ImpersonationToken::where('token', $token)->first();

        if (! $impersonationToken || ! $impersonationToken->isConsumable()) {
            Alert::error(_lang('Error'), _lang('This impersonation link is invalid or has expired.'));

            return redirect()->route('login');
        }

        $targetUser = $impersonationToken->user;

        // The target must still be a real, non-platform institute user. Guards
        // against a token whose account was deleted or elevated after issuing.
        if (! $targetUser || $targetUser->user_type === 'SAAS Admin') {
            $impersonationToken->forceFill(['used_at' => now(), 'used_ip' => $request->ip()])->save();
            Alert::error(_lang('Error'), _lang('This impersonation link is no longer valid.'));

            return redirect()->route('login');
        }

        // Atomically burn the token BEFORE establishing the session. Only the
        // request that flips used_at away from NULL wins the conditional update;
        // a concurrent replay (duplicated tab, double-click, deliberate re-fetch)
        // affects 0 rows and is rejected below. This closes the check-then-act
        // race left by the previous read + isConsumable() + save() sequence, which
        // could mint two sessions from one single-use token.
        $claimed = ImpersonationToken::where('id', $impersonationToken->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now(), 'used_ip' => $request->ip()]);

        if (! $claimed) {
            Alert::error(_lang('Error'), _lang('This impersonation link is invalid or has expired.'));

            return redirect()->route('login');
        }

        // If some other user is already signed in on this browser, drop them
        // first so we never blend two identities into one session.
        if (Auth::check()) {
            Auth::logout();
        }

        Auth::login($targetUser);

        // A fresh session id defeats fixation and cleanly separates this session
        // from anything that preceded it on this browser.
        $request->session()->regenerate();
        $request->session()->put('impersonating', true);
        $request->session()->put('impersonation_initiated_by', $impersonationToken->initiated_by);
        $request->session()->put('impersonation_institute_id', $impersonationToken->institute_id);
        $request->session()->put('impersonation_return_url', $impersonationToken->return_url);

        Log::channel(config('logging.default'))->info('[impersonation] session started', [
            'institute_id' => $impersonationToken->institute_id,
            'target_user_id' => $targetUser->id,
            'initiated_by' => $impersonationToken->initiated_by,
            'ip' => $request->ip(),
        ]);

        Alert::toast(_lang('You are now logged in as').' '.$targetUser->name, 'info');

        return redirect()->route('dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        // Only an actual impersonation session may be "stopped" this way; a
        // normally logged-in institute user hitting this route just gets logged
        // out and bounced to their own login, never to the SaaS panel.
        $wasImpersonating = (bool) $request->session()->get('impersonating');
        $returnUrl = $request->session()->get('impersonation_return_url');
        $initiatedBy = $request->session()->get('impersonation_initiated_by');

        if ($wasImpersonating) {
            Log::channel(config('logging.default'))->info('[impersonation] session stopped', [
                'target_user_id' => Auth::id(),
                'initiated_by' => $initiatedBy,
                'ip' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($wasImpersonating) {
            return redirect()->away($this->returnDestination($returnUrl));
        }

        return redirect()->route('login');
    }

    /**
     * Where "Back to SaaS Admin" lands. Prefers the validated URL captured when
     * the link was minted, falls back to the configured panel URL, and finally
     * to this app's home page — never leaving the operator stranded.
     */
    private function returnDestination(?string $returnUrl): string
    {
        if ($this->isSafePanelUrl($returnUrl)) {
            return $returnUrl;
        }

        $panelUrl = trim((string) config('app.saas_panel_url'));
        if ($panelUrl !== '') {
            return $panelUrl;
        }

        return url('/');
    }

    /**
     * A stored return URL is only trusted when it is absolute http(s) and, if a
     * panel URL is configured, shares that host. The value is server-side (it
     * cannot be tampered with by the browser) but we re-validate anyway.
     */
    private function isSafePanelUrl(?string $url): bool
    {
        if (! is_string($url) || $url === '') {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = parse_url($url, PHP_URL_HOST);

        if (! in_array($scheme, ['http', 'https'], true) || ! $host) {
            return false;
        }

        $panelUrl = trim((string) config('app.saas_panel_url'));
        if ($panelUrl !== '') {
            $allowedHost = parse_url($panelUrl, PHP_URL_HOST);

            return $allowedHost && strcasecmp($allowedHost, $host) === 0;
        }

        return true;
    }
}
