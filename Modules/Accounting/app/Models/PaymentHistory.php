<?php

namespace Modules\Accounting\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\Models\PaymentMethod;

class PaymentHistory extends Model
{
    use BelongsToInstitute;
    use SoftDeletes;

    public const TABLE_NAME = 'payment_histories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'user_id',
        'course_id',
        'payment_method_id',
        'invoice_number',
        'invoice_title',
        'payable',
        'paid',
        'due',
        'status',
        'date_issued',
        'due_date',
        'provider_transaction_id',
        'mobile_payment_provider',
        'confirmation_status',
        'internal_notes',
        'attachments',
        'sent_sms',
        'sent_email',
        'payment_method',
    ];

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
