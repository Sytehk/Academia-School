<?php

namespace Modules\Ai\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Authentication\Models\User;

class AiConversation extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'user_id',
        'feature',
        'title',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class)->orderBy('id');
    }
}
