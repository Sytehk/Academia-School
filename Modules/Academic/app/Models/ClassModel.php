<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassModel extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = ['institute_id', 'branch_id', 'class_name', 'status'];

    public function sections(): HasMany
    {
        // Was previously a belongsTo(StudentSession::class, ...) — wrong model
        // entirely (StudentSession, not Section) and the wrong relation type for
        // a one-class-to-many-sections relationship. Section::class() already
        // points back here correctly; this is its proper inverse.
        return $this->hasMany(Section::class, 'class_id', 'id');
    }

    public function studentSessions(): HasMany
    {
        return $this->hasMany(StudentSession::class, 'class_id', 'id');
    }
}
