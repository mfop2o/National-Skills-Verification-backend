<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortfolioItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'portfolio_items';

    protected $fillable = [
        'portfolio_id', 'type', 'title', 'description', 'organization',
        'issue_date', 'expiry_date', 'credential_id', 'file_path',
        'file_type', 'file_size', 'metadata', 'status'
    ];

    protected $casts = [
        'metadata' => 'array',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'file_size' => 'integer',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Portfolio::class);
    }

    public function verification()
    {
        return $this->hasOne(Verification::class, 'portfolio_item_id');
    }

    public function badges()
    {
        return $this->hasMany(Badge::class, 'verification_id', 'verification_id');
    }

    public function isVerified()
    {
        return $this->status === 'verified';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }
}