<?php

namespace Modules\Library\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryFine extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $guarded = ['id'];
}
