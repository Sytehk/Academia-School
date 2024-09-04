<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class VehicleTaxInfo extends Model
{
    use BelongsToTenant;

    protected $table = 'vehicle_tax_infos';

    protected $fillable = [
        'institute_id', 'branch_id', 'bus_id', 'receipt_number',
        'amount', 'paid_date', 'expiry_date', 'document_path',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }
}
