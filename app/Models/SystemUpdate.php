<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Authentication\Models\User;

class SystemUpdate extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VALIDATING = 'validating';

    public const STATUS_BACKUP = 'backup';

    public const STATUS_INSTALLING = 'installing';

    public const STATUS_MIGRATING = 'migrating';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_ROLLED_BACK = 'rolled_back';

    protected $fillable = [
        'from_version',
        'to_version',
        'type',
        'status',
        'package_name',
        'package_size',
        'started_at',
        'completed_at',
        'backup_path',
        'error_message',
        'executed_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
            'package_size' => 'integer',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SystemUpdateLog::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function duration(): ?int
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }
}
