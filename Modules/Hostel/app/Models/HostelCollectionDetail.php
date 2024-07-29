<?php

namespace Modules\Hostel\Models;

use Illuminate\Database\Eloquent\Model;

class HostelCollectionDetail extends Model
{
    protected $fillable = ['hostel_collection_id', 'fee_head', 'amount', 'is_refundable'];

    protected $casts = ['is_refundable' => 'boolean'];

    public function collection()
    {
        return $this->belongsTo(HostelCollection::class, 'hostel_collection_id');
    }

    public function refunds()
    {
        return $this->hasMany(HostelDepositRefund::class, 'hostel_collection_detail_id');
    }
}
