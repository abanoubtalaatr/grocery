<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    // bad: no $fillable - mass assignment vulnerability
    // bad: no $casts, no docblocks
    public $timestamps = true;
}
