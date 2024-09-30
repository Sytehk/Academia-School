<?php

namespace Modules\Library\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'datetime',
        'code',
        'book_group',
        'book_name',
        'book_copy_no',
        'publisher',
        'publish_year',
        'provider',
        'total_page',
        'identification_page',
        'aa',
        'author',
        'edition',
        'description',
        'category',
        'quantity',
        'price',
        'bookself',
        'rack',
        'path',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function bookCategory(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'category_id', 'id');
    }
}
