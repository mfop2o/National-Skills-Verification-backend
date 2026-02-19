<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge_id', 'user_id', 'issuer_id', 'verification_id',
        'name', 'skill_name', 'description', 'badge_image',
        'level', 'criteria', 'issued_at', 'expires_at', 'status'
    ];

    protected $casts = [
        'criteria' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($badge) {
            $badge->badge_id = 'BDG-' . strtoupper(uniqid());
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issuer_id');
    }

    public function verification()
    {
        return $this->belongsTo(Verification::class);
    }

    public function isValid()
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function revoke($reason)
    {
        $this->status = 'revoked';
        $this->revoke_reason = $reason;
        $this->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'revoke_badge',
            'entity_type' => 'badge',
            'entity_id' => $this->id,
            'new_data' => ['status' => 'revoked', 'reason' => $reason],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}