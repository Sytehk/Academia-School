<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = ['category', 'name', 'icon', 'description'];
}
