<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'address',
        'phone_number',
        'license_number',
        'employment_date',
        'status',
        'profile_picture',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
