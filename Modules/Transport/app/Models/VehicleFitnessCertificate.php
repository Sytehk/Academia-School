<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class VehicleFitnessCertificate extends Model
{
    use BelongsToTenant;

    protected $table = 'vehicle_fitness_certificates';

    protected $fillable = [
        'institute_id', 'branch_id', 'bus_id', 'certificate_number',
        'issue_date', 'expiry_date', 'document_path',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }
}
