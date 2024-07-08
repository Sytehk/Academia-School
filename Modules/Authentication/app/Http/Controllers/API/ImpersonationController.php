<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Authentication\Models\ImpersonationToken;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;

/**
 * "Login as institute" — the SaaS-admin side of impersonation.
 *
 * Mints a short-lived, single-use login link for an institute's admin user and
 * hands the URL back to the panel, which redirects the operator's browser to it
 * (see the WEB\ImpersonationController for the consuming half). No credentials
 * of the institute are ever exposed: the link is the only bearer of trust and
 * it self-destructs after one use or two minutes, whichever comes first.
 *
 * Security posture:
 *   - Only a `SAAS Admin` may call this. The route sits behind `auth:api`, but
 *     that alone would let ANY authenticated institute user mint a link for ANY
 *     institute — a cross-tenant account takeover. The explicit role check below
 *     is the real authorization boundary.
 *   - The impersonation target is always an ordinary institute user (owner /
 *     Admin). A SaaS Admin can never be impersonated, so the link cannot be used
 *     to climb back up to platform-level privilege.
 */
class ImpersonationController extends Controller
{
    public function start(Request $request, int $institute): JsonResponse
    {
        try {
            $actor = $request->user();

            // --- Authorization: SaaS admins only ---------------------------------
            // Defence in depth — the panel also gates this, but the API is the
            // real trust boundary because the bearer token is the credential.
            if (! $actor || $actor->user_type !== 'SAAS Admin') {
                return $this->responseError([], 'You are not authorized to impersonate institutes.', 403);
            }

            $institute = Institute::find($institute);

            if (! $institute) {
                return $this->responseError([], 'Institute not found.', 404);
            }

            // Resolve the account we will log in as: the explicit owner if set,
            // otherwise the institute's earliest Admin. Never a SaaS Admin, and
            // never a soft-deleted or disabled account.
            $target = $this->resolveTarget($institute);

            if (! $target) {
                return $this->responseError([], 'This institute has no active admin user to log in as.', 422);
            }

            // A SaaS Admin must never be a valid impersonation target — that would
            // turn a tenant login into a platform-privilege escalation.
            if ($target->user_type === 'SAAS Admin') {
                return $this->responseError([], 'The resolved account is a platform admin and cannot be impersonated.', 422);
            }

            // Belt and braces: the target must actually belong to this institute.
            if ((int) $target->institute_id !== (int) $institute->id) {
                return $this->responseError([], 'The resolved account does not belong to this institute.', 422);
            }

            // Invalidate any still-live links previously issued for this institute
            // so only the most recent one can ever be consumed.
            ImpersonationToken::where('institute_id', $institute->id)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->update(['used_at' => now()]);

            $token = ImpersonationToken::create([
                'token' => Str::random(64),
                'user_id' => $target->id,
                'institute_id' => $institute->id,
                'initiated_by' => $actor->id,
                'expires_at' => now()->addMinutes(2),
                'return_url' => $this->sanitizeReturnUrl($request->input('return_url')),
            ]);

            Log::channel(config('logging.default'))->info('[impersonation] link issued', [
                'institute_id' => $institute->id,
                'target_user_id' => $target->id,
                'initiated_by' => $actor->id,
                'ip' => $request->ip(),
            ]);

            return $this->responseSuccess([
                'url' => rtrim(config('app.url'), '/')."/impersonate/{$token->token}",
                'expires_at' => $token->expires_at->toIso8601String(),
                'target_user' => ['id' => $target->id, 'name' => $target->name, 'email' => $target->email],
            ], 'Impersonation link generated.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    /**
     * The institute user we will authenticate as. Prefers the declared owner,
     * falls back to the earliest Admin. Only active, non-deleted, non-platform
     * accounts qualify.
     */
    private function resolveTarget(Institute $institute): ?User
    {
        $isEligible = fn (?User $user): bool => $user
            && $user->user_type !== 'SAAS Admin'
            && (int) $user->institute_id === (int) $institute->id
            && $this->isActive($user);

        if ($institute->owner_id) {
            $owner = User::find($institute->owner_id);
            if ($isEligible($owner)) {
                return $owner;
            }
        }

        return User::where('institute_id', $institute->id)
            ->where('user_type', 'Admin')
            ->orderBy('id')
            ->get()
            ->first($isEligible);
    }

    /**
     * A user is considered active unless a `status` column explicitly says
     * otherwise. Kept liberal so schemas without the column still work.
     */
    private function isActive(User $user): bool
    {
        if (! array_key_exists('status', $user->getAttributes())) {
            return true;
        }

        $status = $user->getAttribute('status');

        return $status === null || (int) $status === 1 || $status === true;
    }

    /**
     * Only accept an absolute http(s) return URL, and only when its host matches
     * the configured SaaS panel host. This closes the open-redirect door: the
     * value originates from an authenticated SaaS admin, but we still refuse to
     * store anything we would not be willing to bounce a browser to later.
     */
    private function sanitizeReturnUrl(mixed $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = parse_url($url, PHP_URL_HOST);

        if (! in_array($scheme, ['http', 'https'], true) || ! $host) {
            return null;
        }

        $panelUrl = trim((string) config('app.saas_panel_url'));
        if ($panelUrl !== '') {
            $allowedHost = parse_url($panelUrl, PHP_URL_HOST);
            if ($allowedHost && strcasecmp($allowedHost, $host) !== 0) {
                return null;
            }
        }

        return $url;
    }
}
