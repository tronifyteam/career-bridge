<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory, \App\Traits\LogsActivity;

    protected $table = 'job_listings';

    protected $fillable = [
        'employer_id',
        'title',
        'employer_name',
        'employer_type',
        'location',
        'salary',
        'salary_period',
        'employment_type',
        'working_hours_and_rest_days',
        'worker_count',
        'employment_period',
        'dormitory_meals_deductions',
        'contact_method',
        'mask_contact_info',
        'tags',
        'category',
        'job_type_id',
        'description',
        'duties',
        'requirements',
        'benefits',
        'hours',
        'language',
        'legal_status',
        'eligibility',
        'verification_required',
        'is_urgent',
        'status',
        'risk_level',
        'red_flags',
        'missing_fields',
        'screened_at',
        'rejection_reason',
        'posted_at',
        'expires_at',
        'employer_authorization_url',
        'job_source_proof_url',
        'fee_table_url',
        'is_sponsored',
        'sponsored_until',
    ];

    protected function casts(): array
    {
        return [
            'tags'                  => 'array',
            'red_flags'             => 'array',
            'missing_fields'        => 'array',
            'is_urgent'             => 'boolean',
            'verification_required' => 'boolean',
            'mask_contact_info'     => 'boolean',
            'is_sponsored'          => 'boolean',
            'worker_count'          => 'integer',
            'posted_at'             => 'datetime',
            'expires_at'            => 'datetime',
            'screened_at'           => 'datetime',
            'sponsored_until'       => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function jobType()
    {
        return $this->belongsTo(JobType::class, 'job_type_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function applicants()
    {
        return $this->hasManyThrough(
            User::class,
            JobApplication::class,
            'job_id',
            'id',
            'id',
            'user_id'
        );
    }

    // ── Scopes ────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function scopeByCategory($query, string|array $category)
    {
        if (is_array($category)) {
            return $query->whereIn('category', $category);
        }
        return $query->where('category', $category);
    }

    public function scopeByCity($query, string|array $city)
    {
        if (is_array($city)) {
            return $query->whereIn('location', $city);
        }
        return $query->where('location', $city);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('employer_name', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeByEligibility($query, string|array $eligibility)
    {
        if (is_array($eligibility)) {
            return $query->whereIn('eligibility', $eligibility);
        }
        return $query->where('eligibility', $eligibility);
    }

    public function scopeByJobType($query, int|string|array $jobTypeId)
    {
        if (is_array($jobTypeId)) {
            return $query->whereIn('job_type_id', $jobTypeId);
        }
        return $query->where('job_type_id', $jobTypeId);
    }

    public function scopeByLanguage($query, string|array $language)
    {
        if (is_array($language)) {
            return $query->where(function ($q) use ($language) {
                foreach ($language as $lang) {
                    $q->orWhere('language', 'like', "%{$lang}%");
                }
            });
        }
        return $query->where('language', 'like', "%{$language}%");
    }

    public function scopeBySalaryRange($query, ?float $min, ?float $max)
    {
        // Salary is stored as string (e.g. "30000 TWD"), cast numerically for range
        if ($min !== null) {
            $query->whereRaw('CAST(salary AS DECIMAL(12,2)) >= ?', [$min]);
        }
        if ($max !== null) {
            $query->whereRaw('CAST(salary AS DECIMAL(12,2)) <= ?', [$max]);
        }
        return $query;
    }

    // ── API Serialization ─────────────────────────────────

    /**
     * Returns the job data for API responses.
     * For Family Care employers, sensitive fields (employer name, exact address)
     * are masked to protect household privacy (UAT #17).
     *
     * @param  bool $isOwner  Pass true only when the authenticated user is the employer (no masking).
     */
    public function toApiArray(bool $isOwner = false): array
    {
        $shouldMask = ! $isOwner;

        // For all jobs, show only the district/city level for location to protect privacy
        $maskedLocation = $shouldMask
            ? collect(explode(',', $this->location ?? ''))->last(null, $this->location) // last comma segment
            : $this->location;
            
        $maskedEmployerName = $shouldMask ? ucfirst(strtolower($this->employer_type ?? 'Employer')) . ' (Private)' : $this->employer_name;

        return [
            'id'                   => (string) $this->id,
            'title'                => $this->title,
            // Mask employer name for workers
            'employer_name'        => $maskedEmployerName,
            'employer_type'        => $this->employer_type,
            // Show only district/city for workers
            'location'             => trim($maskedLocation ?? $this->location ?? ''),
            'salary'               => $this->salary,
            'salary_period'        => $this->salary_period,
            'employment_type'      => $this->employment_type,
            'working_hours_and_rest_days' => $this->working_hours_and_rest_days,
            'worker_count'         => $this->worker_count,
            'employment_period'    => $this->employment_period,
            'dormitory_meals_deductions' => $this->dormitory_meals_deductions,
            'contact_method'       => ($this->mask_contact_info && $shouldMask) ? 'Hidden (Apply via platform)' : $this->contact_method,
            'mask_contact_info'    => (bool) $this->mask_contact_info,
            'is_masked'            => $shouldMask, // frontend can show warning badge
            'tags'                 => $this->tags ?? [],
            'category'             => $this->category,
            'job_type_id'          => $this->job_type_id,
            'job_type_name'        => $this->jobType?->job_type_name,
            'description'          => $this->description ?? '',
            'duties'               => $this->duties ?? '',
            'requirements'         => $this->requirements ?? '',
            'benefits'             => $this->benefits ?? '',
            'hours'                => $this->hours ?? '',
            'language'             => $this->language ?? '',
            'legal_status'         => $this->legal_status ?? '',
            'eligibility'          => $this->eligibility ?? 'All',
            'verification_required'=> (bool) ($this->verification_required ?? true),
            'is_urgent'            => $this->is_urgent,
            'is_sponsored'         => $this->is_sponsored,
            'sponsored_until'      => $this->sponsored_until?->toIso8601String(),
            'posted_at'            => $this->posted_at?->toIso8601String() ?? $this->created_at->toIso8601String(),
            'expires_at'           => $this->expires_at?->toIso8601String(),
            'status'               => $this->status,
            'risk_level'           => $this->risk_level ?? 'low',
            'rejection_reason'     => $this->rejection_reason,
            'employer_id'          => (string) $this->employer_id,
            'applications_count'   => $this->applications_count ?? $this->applications()->count(),
        ];
    }
}
