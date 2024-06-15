<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;

/**
 * Tenant ownership rules for tables that do NOT follow the default
 * institute_id + branch_id pattern.
 *
 *  - SHARED:        institute-owned, but rows with a NULL institute_id form a
 *                   platform-wide shared library every institute may READ.
 *                   Only a SAAS Admin may change a shared row.
 *  - INSTITUTE:     institute-owned, no branch column.
 *
 * Used by EntityRepository (raw query builder), the SharedWithInstitute model
 * trait and the `tenant_exists` validation rule, so all three agree.
 */
final class TenantTables
{
    public const SHARED = [
        'question_bank_classes',
        'question_bank_groups',
        'question_bank_subjects',
        'question_bank_chapters',
        'question_bank_topics',
        'question_bank_types',
        'question_bank_levels',
        'question_bank_sources',
        'question_bank_sub_sources',
        'question_bank_tags',
        'question_bank_sessions',
        'question_bank_years',
        'question_bank_boards',
        'question_bank_difficulty_levels',
        'question_bank_tests',
    ];

    public const INSTITUTE = [
        'questions',
        'question_categories',
        'question_pools',
    ];

    public static function isShared(string $table): bool
    {
        return in_array($table, self::SHARED, true);
    }

    public static function isInstituteOnly(string $table): bool
    {
        return in_array($table, self::INSTITUTE, true);
    }

    public static function isSaasAdmin(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'SAAS Admin';
    }

    /**
     * Restrict a query to the rows the current user may READ.
     */
    public static function applyReadScope(Builder|EloquentBuilder $query, string $table): void
    {
        $instituteId = get_institute_id();

        if (self::isShared($table)) {
            if (self::isSaasAdmin() && ! $instituteId) {
                return;
            }
            $query->where(function ($inner) use ($table, $instituteId) {
                $inner->whereNull($table.'.institute_id');
                if ($instituteId) {
                    $inner->orWhere($table.'.institute_id', $instituteId);
                }
            });

            return;
        }

        if ($instituteId) {
            $query->where($table.'.institute_id', $instituteId);
        }

        if (! self::isInstituteOnly($table) && ($branchId = get_branch_id())) {
            $query->where($table.'.branch_id', $branchId);
        }
    }

    /**
     * Restrict a query to the rows the current user may CHANGE: never a shared
     * (NULL-institute) row unless the user is the platform's SAAS Admin.
     */
    public static function applyWriteScope(Builder|EloquentBuilder $query, string $table): void
    {
        if (self::isShared($table) && self::isSaasAdmin()) {
            return;
        }

        $query->where($table.'.institute_id', get_institute_id());
    }

    public static function canWrite(string $table, mixed $rowInstituteId): bool
    {
        if ($rowInstituteId === null) {
            return self::isShared($table) && self::isSaasAdmin();
        }

        return (int) $rowInstituteId === (int) get_institute_id() || self::isSaasAdmin();
    }
}
