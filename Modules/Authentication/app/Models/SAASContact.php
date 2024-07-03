<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SAASContact extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'user_id', 'isSeen'];

    public const TABLE_NAME = 's_a_a_s_contacts';

    protected $table = self::TABLE_NAME;
}
