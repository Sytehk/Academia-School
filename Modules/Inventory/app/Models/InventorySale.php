<?php

namespace Modules\Inventory\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;

class InventorySale extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'institute_id', 'branch_id', 'student_id', 'invoice_id', 'sale_date',
        'total_payable', 'total_paid', 'total_due', 'ledger_id',
        'receive_ledger_id', 'fund_id', 'note', 'created_by',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_payable' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'total_due' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventorySaleItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
