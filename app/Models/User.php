<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'status',
        'fayda_id', 'region', 'city', 'woreda', 'kebele', 'languages',
        'institution_name', 'institution_type', 'accreditation_number', 'is_verified_institution',
        'company_name', 'company_registration', 'is_verified_employer'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'languages' => 'array',
        'is_verified_institution' => 'boolean',
        'is_verified_employer' => 'boolean',
    ];

    // Relationships
    public function portfolio()
    {
        return $this->hasOne(Portfolio::class);
    }

    public function portfolioItems()
    {
        return $this->hasManyThrough(PortfolioItem::class, Portfolio::class);
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class, 'institution_id');
    }

    public function verifiedItems()
    {
        return $this->hasMany(Verification::class, 'verified_by');
    }

    public function badges()
    {
        return $this->hasMany(Badge::class);
    }

    public function issuedBadges()
    {
        return $this->hasMany(Badge::class, 'issuer_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
                    ->withPivot('proficiency', 'years_experience', 'verification_status', 'verified_at')
                    ->withTimestamps();
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function institution()
    {
        return $this->hasOne(Institution::class);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isInstitution()
    {
        return $this->role === 'institution';
    }

    public function isEmployer()
    {
        return $this->role === 'employer';
    }

    public function isVerifiedInstitution()
    {
        return $this->is_verified_institution && $this->institution && $this->institution->approval_status === 'approved';
    }

    public function canVerify()
    {
        return $this->isInstitution() && $this->isVerifiedInstitution();
    }
}