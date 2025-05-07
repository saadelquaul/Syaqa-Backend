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
        'image_url',
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
        return $this->belongsTo(Monitor::class,);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'candidate_id')
                    ->withPivot('status', 'enrolled_at', 'completed_at')
                    ->withTimestamps();
    }



}
