<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'bio', 'profile_photo', 'cover_photo',
        'social_links', 'visibility', 'views_count'
    ];

    protected $casts = [
        'social_links' => 'array',
        'views_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PortfolioItem::class);
    }

    public function certificates()
    {
        return $this->hasMany(PortfolioItem::class)->where('type', 'certificate');
    }

    public function projects()
    {
        return $this->hasMany(PortfolioItem::class)->where('type', 'project');
    }

    public function workExperiences()
    {
        return $this->hasMany(PortfolioItem::class)->where('type', 'work_experience');
    }
}