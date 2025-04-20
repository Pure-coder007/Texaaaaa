<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable, HasApiTokens, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'admin_role',
        'status',
        'pbo_level_id',
        'custom_direct_commission_percentage',
        'custom_referral_commission_percentage',
        'use_custom_commission',
        'onboarding_completed',

        // Personal Information
        'spouse_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'languages_spoken',

        // Contact Information
        'address',
        'country_of_residence',
        'mobile_number',

        // Employment Details
        'occupation',
        'employer_name',

        // Next of Kin Details
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_address',
        'next_of_kin_phone',
        'next_of_kin_email',

        // Terms & Submission
        'terms_accepted',
        'submission_date',
        'registration_completed',

        'pbo_code',


        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_branch',
        'bank_swift_code',
        'preferred_payment_method',
        'payment_notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'languages_spoken' => 'array',
        'date_of_birth' => 'date',
        'submission_date' => 'date',
        'terms_accepted' => 'boolean',
        'registration_completed' => 'boolean',
    ];

    protected array $guard_name = ['admin', 'pbo', 'web'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'languages_spoken' => 'array',
            'date_of_birth' => 'date',
            'submission_date' => 'date',
            'terms_accepted' => 'boolean',
            'registration_completed' => 'boolean',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar_url')
            ->singleFile();
    }

    // PBO Level relationship
    public function pboLevel()
    {
        return $this->belongsTo(PboLevel::class);
    }

    // Estates managed by this user (if admin/manager)
    public function managedEstates()
    {
        return $this->hasMany(Estate::class, 'manager_id');
    }

    // Purchases made by this user (if client)
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'client_id');
    }

    // Referrals made by this user (if PBO)
    public function referrals()
    {
        return $this->hasMany(PboReferral::class, 'referrer_id');
    }

    // Referral that brought this user
    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    // Users referred by this user
    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    // Sales where this user earned commission (if PBO)
    public function pboSales()
    {
        return $this->hasMany(PboSale::class, 'pbo_id');
    }

    // Inspections scheduled by this user
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'client_id');
    }

    // Client folders owned by this user
    public function clientFolders()
    {
        return $this->hasMany(ClientFolder::class, 'client_id');
    }

    // Payments made by this user (if client)
    public function payments()
    {
        return $this->hasMany(Payment::class, 'client_id');
    }

     // Referrer relationship
     public function referrer()
     {
         return $this->belongsTo(User::class, 'referred_by');
     }


     // Agent referral records as referrer
     public function agentReferrals()
     {
         return $this->hasMany(PboReferral::class, 'referrer_id');
     }

     // Total points earned by this agent
     public function pboPoints()
     {
         return $this->hasMany(PboPoint::class, 'pbo_id');
     }

     // Get total points
     public function getTotalPointsAttribute()
     {
         return $this->pboPoints()->sum('points');
     }

     // Generate a unique agent code if not already set
     public function generateAgentCode()
     {
         if (!$this->pbo_code) {
             $code = strtoupper(substr(str_replace(' ', '', $this->name), 0, 2) . rand(1000, 9999));

             // Ensure code is unique
             while (User::where('pbo_code', $code)->exists()) {
                 $code = strtoupper(substr(str_replace(' ', '', $this->name), 0, 2) . rand(1000, 9999));
             }

             $this->update(['pbo_code' => $code]);
         }

         return $this->pbo_code;
     }

     // Get the referral URL for this agent
     public function getReferralUrlAttribute()
     {
         if (!$this->pbo_code) {
             $this->generateAgentCode();
         }

         return route('filament.agent.auth.register', ['ref' => $this->pbo_code]);
     }

     // Add points to this agent
     public function addPoints(int $points, string $type, ?string $description = null)
     {
         return $this->pboPoints()->create([
             'points' => $points,
             'type' => $type,
             'description' => $description,
         ]);
     }

     public function hasBankDetails(): bool
     {
         return !empty($this->bank_name) && !empty($this->bank_account_number) && !empty($this->bank_account_name);
     }

}
