<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'level',
        'description',
        'is_verified'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
