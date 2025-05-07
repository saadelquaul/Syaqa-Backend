<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'course_id',
        'candidate_id',
        'status',
        'enrolled_at',
        'completed_at'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
