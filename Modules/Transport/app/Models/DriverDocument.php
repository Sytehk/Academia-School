<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    use BelongsToTenant;

    protected $table = 'driver_documents';

    protected $fillable = ['institute_id', 'branch_id', 'driver_id', 'document_type', 'file_path', 'expiry_date'];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
