<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['institute_id', 'name', 'status'];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }
}
