<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;

class InstituteHelper
{
    /**
     * Resolve the active institute from the current request host.
     */
    public static function resolveInstituteFromRequest(Request $request): ?Institute
    {
        if (! Schema::hasTable('institutes')) {
            return null;
        }

        $host = strtolower((string) $request->getHost());
        $host = trim($host, ". \t\n\r\0\x0B");
        $host = str_replace(['http://', 'https://'], '', $host);

        if ($host === '') {
            return Institute::where('status', 1)->first();
        }

        $normalizedHost = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        return Institute::query()
            ->where('status', 1)
            ->where(function ($query) use ($host, $normalizedHost): void {
                $query->where('domain', $host)
                    ->orWhere('domain', $normalizedHost)
                    ->orWhere('domain', 'www.'.$normalizedHost);
            })
            ->first() ?? Institute::where('status', 1)->first();
    }

    /**
     * Resolve the primary branch for the active institute from the request context.
     */
    public static function resolveBranchIdFromRequest(Request $request): ?int
    {
        if (! Schema::hasTable('branches')) {
            return null;
        }

        $institute = self::resolveInstituteFromRequest($request);

        if (! $institute) {
            return null;
        }

        return Branch::query()
            ->where('institute_id', $institute->id)
            ->where('status', 1)
            ->value('id');
    }

    /**
     * Get institute ID based on the domain.
     *
     * @return mixed
     */
    public static function getInstituteIdByDomain(string $domain)
    {
        // Check if domain is provided
        if (empty($domain)) {
            return [
                'error' => 'Domain is required.',
                'status' => false,
            ];
        }

        // Check if domain format is valid (e.g., example.com)
        if (! filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'error' => 'Invalid domain format.',
                'status' => false,
            ];
        }

        // Fetch the institute based on the domain
        $institute = DB::table('institutes')
            ->where('domain', $domain)
            ->select('id')
            ->first();

        // If no matching institute is found
        if (! $institute) {
            return [
                'error' => 'Institute not found for the provided domain.',
                'status' => false,
            ];
        }

        // Return the institute ID
        return [
            'institute_id' => $institute->id,
            'status' => true,
        ];
    }
}
