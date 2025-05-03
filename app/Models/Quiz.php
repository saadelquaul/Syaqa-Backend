<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{

    protected $fillable = [
        'correct_answers',
        'incorrect_answers',
        'total_questions',
        'score',
        'candidate_id',
    ];


    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
