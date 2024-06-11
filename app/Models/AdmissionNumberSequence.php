<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One row per institute: the next admission number to hand out. */
class AdmissionNumberSequence extends Model
{
    protected $fillable = [
        'institute_id',
        'next_number',
    ];
}
