<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'employment_date',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
