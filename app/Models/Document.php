<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'candidate_id',
        'CIN',
        'cin_type',
        ];
}
