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
        'monitor_id',
        'image',
        'video_url',
        'status',
        'slug',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function monitor()
    {
        return $this->belongsTo(User::class, 'monitor_id');
    }

    

}
