<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_number', 'portfolio_item_id', 'institution_id',
        'verified_by', 'status', 'remarks', 'rejection_reason',
        'verified_at', 'verification_data'
    ];

    protected $casts = [
        'verification_data' => 'array',
        'verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($verification) {
            $verification->verification_number = 'VRF-' . strtoupper(uniqid());
        });
    }

    public function portfolioItem()
    {
        return $this->belongsTo(PortfolioItem::class);
    }

    public function institution()
    {
        return $this->belongsTo(User::class, 'institution_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function badge()
    {
        return $this->hasOne(Badge::class);
    }

    public function approve($verifierId, $remarks = null)
    {
        $this->status = 'approved';
        $this->verified_by = $verifierId;
        $this->verified_at = now();
        $this->remarks = $remarks;
        $this->save();

        // Update portfolio item status
        $this->portfolioItem->status = 'verified';
        $this->portfolioItem->save();

        // Create audit log
        AuditLog::create([
            'user_id' => $verifierId,
            'action' => 'approve_verification',
            'entity_type' => 'verification',
            'entity_id' => $this->id,
            'new_data' => ['status' => 'approved', 'remarks' => $remarks],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public function reject($verifierId, $reason)
    {
        $this->status = 'rejected';
        $this->verified_by = $verifierId;
        $this->rejection_reason = $reason;
        $this->verified_at = now();
        $this->save();

        $this->portfolioItem->status = 'rejected';
        $this->portfolioItem->save();

        AuditLog::create([
            'user_id' => $verifierId,
            'action' => 'reject_verification',
            'entity_type' => 'verification',
            'entity_id' => $this->id,
            'new_data' => ['status' => 'rejected', 'reason' => $reason],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}