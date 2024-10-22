<?php

namespace Modules\SystemConfiguration\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Authentication\Models\User;

class UserLog extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'user_id',
        'ip_address',
        'action',
        'detail',
        'previous_detail',
        'model',
        'model_id',
        'url',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')->select('id', 'name');
    }
}
