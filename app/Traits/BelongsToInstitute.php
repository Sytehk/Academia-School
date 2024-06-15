<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Institute-only sibling of {@see BelongsToTenant} for models whose table has an
 * institute_id column but NO branch_id (e.g. question bank items). Auto-scopes
 * every query to the logged-in user's institute and stamps institute_id on
 * create when it is not already set.
 *
 * To query across institutes (platform-level SAAS Admin tooling), use
 * Model::withoutGlobalScope('institute') or ::withoutGlobalScopes().
 */
trait BelongsToInstitute
{
    protected static function bootBelongsToInstitute(): void
    {
        static::addGlobalScope('institute', function (Builder $builder) {
            if (! auth()->check()) {
                return;
            }

            $instituteId = get_institute_id();

            if ($instituteId) {
                $builder->where($builder->getModel()->getTable().'.institute_id', $instituteId);
            }
        });

        static::creating(function (Model $model) {
            if (auth()->check() && is_null($model->institute_id)) {
                $model->institute_id = get_institute_id();
            }
        });
    }
}
