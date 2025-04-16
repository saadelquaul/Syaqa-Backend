<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image'
    ];

    /**
     * Get all courses that belong to this category.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
