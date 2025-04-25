<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'candidate_id',
        'monitor_id',
        'date',
        'start_time',
        'end_time',
        'lesson_type',
        'status',
        'notes'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
    public function monitor()
    {
        return $this->belongsTo(Monitor::class);
    }

}
