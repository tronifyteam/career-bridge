<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, \App\Traits\LogsActivity;

    protected $fillable = [
        'name',
        'full_name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'nationality',
        'current_city',
        'company_name',
        'industry',
        'profile_completed',
        'onboarding_step',
        'avatar_url',
        'phone',
        'phone_verified_at',
        'license_number',
        'license_expiry_date',
        'unified_business_number',
        'verification_status',
        'verification_note',
        'cv_url',
        'selfie_file_url',
        'selfie_verified_at',
        'preferred_language',
        'date_of_birth',
        'gender',
        'address',
        'educations',
        'work_experiences',
        'skills',
        'fcm_token',
        'provider_name',
        'provider_id',
        // Worker fields
        'worker_type',
        'worker_type_id',
        'current_work_status',
        'language_abilities',
        'is_cv_public',
        'available_date',
        'expected_salary',
        'notification_preferences',
        'verified_badge_status',
        'verified_badge_updated_at',
        'ready_to_work_status',
        'ready_to_work_updated_at',
        'open_work_right_status',
        'employer_self_check_required',
        'trust_score',
        'violation_count',
    ];

    protected $hidden = ['password', 'remember_token', 'fcm_token', 'provider_id'];

    protected function casts(): array
    {
        return [
            'email_verified_at'           => 'datetime',
            'phone_verified_at'           => 'datetime',
            'selfie_verified_at'          => 'datetime',
            'verified_badge_updated_at'   => 'datetime',
            'ready_to_work_updated_at'    => 'datetime',
            'password'                    => 'hashed',
            'profile_completed'           => 'boolean',
            'is_admin'                    => 'boolean',
            'is_cv_public'                => 'boolean',
            'sponsorship_required'        => 'boolean',
            'employer_self_check_required'=> 'boolean',
            'date_of_birth'               => 'date',
            'license_expiry_date'         => 'date',
            'available_date'              => 'date',
            'expected_salary'             => 'decimal:2',
            'educations'                  => 'array',
            'work_experiences'            => 'array',
            'skills'                      => 'array',
            'language_abilities'          => 'array',
            'notification_preferences'    => 'array',
        ];
    }

    // ── Role Helpers ──────────────────────────────────────

    public function getRoleIdAttribute(): ?string
    {
        return match ($this->role) {
            'worker'       => 'ROLE_WORKER',
            'company'      => 'ROLE_COMPANY',
            'factory'      => 'ROLE_FACTORY',
            'family_care'  => 'ROLE_FAMILY_CARE',
            'agency'       => 'ROLE_AGENCY',
            'agency_staff' => 'ROLE_AGENCY_STAFF',
            default        => null,
        };
    }

    public static function roleFromRoleId(string $roleId): ?string
    {
        return match ($roleId) {
            'ROLE_WORKER'       => 'worker',
            'ROLE_COMPANY'      => 'company',
            'ROLE_FACTORY'      => 'factory',
            'ROLE_FAMILY_CARE'  => 'family_care',
            'ROLE_AGENCY'       => 'agency',
            'ROLE_AGENCY_STAFF' => 'agency_staff',
            default             => null,
        };
    }

    public function isEmployer(): bool
    {
        return in_array($this->role, ['company', 'factory', 'family_care', 'agency', 'agency_staff']);
    }

    public function isWorker(): bool
    {
        return $this->role === 'worker';
    }

    public function isAgency(): bool
    {
        return $this->role === 'agency';
    }

    public function isVerifiedEmployer(): bool
    {
        if (!$this->isEmployer()) {
            return false;
        }

        if ($this->role === 'agency_staff') {
            $staff = $this->employerStaff;
            return $staff && $staff->status === 'approved'
                && $staff->agency && $staff->agency->verified_badge_status === 'verified';
        }

        return $this->ready_to_work_status === 'ready'
            || in_array($this->verification_status, ['basic_verified', 'manually_verified'])
            || $this->verified_badge_status === 'verified';
    }

    public function hasVerifiedBadge(): bool
    {
        return $this->verified_badge_status === 'verified';
    }

    public function isReadyToWork(): bool
    {
        return $this->ready_to_work_status === 'ready';
    }

    // ── Relationships ─────────────────────────────────────

    public function workerTypeModel()
    {
        return $this->belongsTo(WorkerType::class, 'worker_type_id');
    }

    public function workerLanguages()
    {
        return $this->hasMany(WorkerLanguage::class, 'user_id');
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'worker_languages', 'user_id', 'language_id')
                    ->withPivot('proficiency_level')
                    ->withTimestamps();
    }

    public function workerJobTypes()
    {
        return $this->hasMany(WorkerJobType::class, 'user_id');
    }

    public function jobTypes()
    {
        return $this->belongsToMany(JobType::class, 'worker_job_types', 'user_id', 'job_type_id')
                    ->withPivot('years_of_experience')
                    ->withTimestamps();
    }

    public function workerDocuments()
    {
        return $this->hasMany(WorkerDocument::class, 'user_id');
    }

    public function documentRequirements()
    {
        return $this->hasMany(WorkerDocumentRequirement::class, 'user_id');
    }

    public function employerStaff()
    {
        return $this->hasOne(EmployerStaff::class, 'user_id');
    }

    public function agencyStaff()
    {
        return $this->hasMany(EmployerStaff::class, 'agency_employer_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployerDocument::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
                    ->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'employer_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeEmployers($query)
    {
        return $query->whereIn('role', ['company', 'factory', 'family_care', 'agency', 'agency_staff']);
    }

    public function scopeWorkers($query)
    {
        return $query->where('role', 'worker');
    }

    public function scopeReadyToWork($query)
    {
        return $query->where('ready_to_work_status', 'ready');
    }

    public function scopeVerifiedBadge($query)
    {
        return $query->where('verified_badge_status', 'verified');
    }

    // ── API Serialization ─────────────────────────────────

    public function toApiArray(): array
    {
        $data = [
            'id'                          => (string) $this->id,
            'email'                       => $this->email,
            'full_name'                   => $this->full_name ?? $this->name,
            'role_id'                     => $this->role_id,
            'role'                        => $this->role,
            'onboarding_step'             => (int) ($this->onboarding_step ?? 1),
            'profile_completed'           => (bool) $this->profile_completed,
            'nationality'                 => $this->nationality,
            'current_city'                => $this->current_city,
            'company_name'                => $this->company_name,
            'unified_business_number'     => $this->unified_business_number,
            'industry'                    => $this->industry,
            'avatar_url'                  => $this->avatar_url,
            'phone'                       => $this->phone,
            'email_verified'              => $this->email_verified_at !== null,
            'phone_verified'              => $this->phone_verified_at !== null,
            'license_number'              => $this->license_number,
            // Legacy employer verification
            'verification_status'         => $this->verification_status ?? 'unverified',
            'verification_note'           => $this->verification_note,
            // Badge system
            'verified_badge_status'       => $this->verified_badge_status ?? 'unverified',
            'verified_badge_updated_at'   => $this->verified_badge_updated_at?->toIso8601String(),
            'ready_to_work_status'        => $this->ready_to_work_status ?? 'not_ready',
            'ready_to_work_updated_at'    => $this->ready_to_work_updated_at?->toIso8601String(),
            'open_work_right_status'      => $this->open_work_right_status,
            'employer_self_check_required'=> (bool) $this->employer_self_check_required,
            'trust_score'                 => (int) ($this->trust_score ?? 100),
            'violation_count'             => (int) ($this->violation_count ?? 0),
            // Documents
            'cv_url'                      => $this->cv_url,
            'selfie_file_url'             => $this->selfie_file_url,
            'selfie_verified'             => $this->selfie_verified_at !== null,
            // Worker profile
            'preferred_language'          => $this->preferred_language,
            'date_of_birth'               => $this->date_of_birth ? $this->date_of_birth->format('Y-m-d') : null,
            'gender'                      => $this->gender,
            'address'                     => $this->address,
            'educations'                  => $this->educations ?? [],
            'work_experiences'            => $this->work_experiences ?? [],
            'skills'                      => $this->skills ?? [],
            'worker_type'                 => $this->worker_type,
            'worker_type_id'              => $this->worker_type_id,
            'current_work_status'         => $this->current_work_status,
            'language_abilities'          => $this->language_abilities ?? [],
            'is_cv_public'                => (bool) ($this->is_cv_public ?? true),
            'available_date'              => $this->available_date ? $this->available_date->format('Y-m-d') : null,
            'expected_salary'             => $this->expected_salary,
        ];

        if ($this->isWorker()) {
            $data['worker_documents'] = $this->workerDocuments->map->toApiArray()->values()->toArray();
        } elseif ($this->isEmployer()) {
            $data['employer_documents'] = $this->documents->map->toApiArray()->values()->toArray();
        }

        return $data;
    }

    /**
     * Minimal public profile for worker directory (employer view).
     */
    public function toPublicProfileArray(): array
    {
        $languages = $this->workerLanguages->map->toApiArray()->values();
        $jobTypes  = $this->workerJobTypes->map->toApiArray()->values();

        return [
            'id'                          => (string) $this->id,
            'full_name'                   => $this->full_name ?? $this->name,
            'nationality'                 => $this->nationality,
            'current_city'                => $this->current_city,
            'avatar_url'                  => $this->avatar_url,
            'worker_type'                 => $this->worker_type,
            'worker_type_id'              => $this->worker_type_id,
            'verified_badge_status'       => $this->verified_badge_status ?? 'unverified',
            'ready_to_work_status'        => $this->ready_to_work_status ?? 'not_ready',
            'employer_self_check_required'=> (bool) $this->employer_self_check_required,
            'trust_score'                 => (int) ($this->trust_score ?? 100),
            'available_date'              => $this->available_date ? $this->available_date->format('Y-m-d') : null,
            'expected_salary'             => $this->expected_salary,
            'languages'                   => $languages,
            'job_types'                   => $jobTypes,
            'cv_url'                      => $this->is_cv_public ? $this->cv_url : null,
        ];
    }
}
