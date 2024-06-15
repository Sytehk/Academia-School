<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Automatically scopes every query on the model to the logged-in user's institute/branch,
 * and stamps institute_id/branch_id on create when they aren't already set.
 *
 * Use on any model whose table has both institute_id and branch_id columns. This exists
 * because tenant scoping in this codebase was previously applied by hand per-query
 * (get_institute_id()/get_branch_id() calls scattered through controllers), which is easy
 * to miss — see the Hostel module, where every store() scoped correctly but every index()
 * did not. Attaching this trait to the model closes that class of bug at the source instead
 * of requiring every call site to remember.
 *
 * To intentionally query across tenants (platform-level SAAS Admin tooling only), use
 * Model::withoutGlobalScope('tenant') or ::withoutGlobalScopes().
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (! auth()->check()) {
                return;
            }

            $table = $builder->getModel()->getTable();
            $instituteId = get_institute_id();
            $branchId = get_branch_id();

            if ($instituteId) {
                $builder->where($table.'.institute_id', $instituteId);
            }
            if ($branchId) {
                $builder->where($table.'.branch_id', $branchId);
            }
        });

        static::creating(function (Model $model) {
            if (! auth()->check()) {
                return;
            }

            if (is_null($model->institute_id)) {
                $model->institute_id = get_institute_id();
            }
            if (is_null($model->branch_id)) {
                $model->branch_id = get_branch_id();
            }
        });
    }
}
