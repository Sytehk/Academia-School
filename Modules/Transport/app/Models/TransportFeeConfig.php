<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportFeeConfig extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'transport_fee_configs';

    protected $fillable = [
        'institute_id', 'branch_id', 'session_id', 'route_id',
        'stop_id', 'monthly_fee', 'fund_id', 'ledger_id',
    ];

    public function route()
    {
        return $this->belongsTo(BusRoute::class, 'route_id');
    }

    public function stop()
    {
        return $this->belongsTo(BusStop::class, 'stop_id');
    }
}
