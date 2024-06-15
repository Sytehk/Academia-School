<?php

namespace App\Traits;

use Exception;

trait Authenticatable
{
    /**
     * A genuinely resolved authenticated user always wins over the console
     * fallback below — `php artisan test` also satisfies runningInConsole(),
     * so a simulated HTTP request with a real Passport/session user must not
     * be treated the same as a true console command (seeder/queued job) that
     * has no request/user at all. Checking this first preserves the original
     * fallback-to-1 behavior for genuine console use (no request/user exists
     * either way) while fixing owner/tenant stamping under automated tests.
     *
     * @throws Exception
     */
    protected function getCurrentUserId(): int
    {
        if (isset(request()->user()->id)) {
            return (int) request()->user()->id;
        }

        if (app()->runningInConsole()) {
            return 1;
        }

        throw new Exception('You are not authenticated to view this.');
    }

    /**
     * @throws Exception
     */
    protected function getCurrentInstituteId(): int
    {
        if (isset(request()->user()->institute_id)) {
            return (int) request()->user()->institute_id;
        }

        if (app()->runningInConsole()) {
            return 1;
        }

        throw new Exception('You are not authenticated to view this.');
    }

    /**
     * @throws Exception
     */
    protected function getCurrentBranchId(): int
    {
        if (isset(request()->user()->branch_id)) {
            return (int) request()->user()->branch_id;
        }

        if (app()->runningInConsole()) {
            return 1;
        }

        throw new Exception('You are not authenticated to view this.');
    }
}
