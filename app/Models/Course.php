<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'description',
        'duration',
        'category_id',
        'category_id',
        'instructor_id',
        'instructor_id',
        'image',
        'content',
        'status',
        'slug',
    ];
    
}
