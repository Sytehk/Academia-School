<?php

namespace App\Traits;

use App\Support\TenantTables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent counterpart of TenantTables::SHARED: an institute sees its own rows
 * plus the platform's shared (NULL-institute) rows, and new rows are owned by
 * the creating institute.
 */
trait SharedWithInstitute
{
    protected static function bootSharedWithInstitute(): void
    {
        static::addGlobalScope('institute_shared', function (Builder $builder) {
            if (! auth()->check()) {
                return;
            }

            TenantTables::applyReadScope($builder, $builder->getModel()->getTable());
        });

        static::creating(function (Model $model) {
            if (auth()->check() && is_null($model->institute_id) && ! TenantTables::isSaasAdmin()) {
                $model->institute_id = get_institute_id();
            }
        });
    }

    public function isShared(): bool
    {
        return $this->institute_id === null;
    }
}
