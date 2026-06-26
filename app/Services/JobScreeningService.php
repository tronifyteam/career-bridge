<?php

namespace App\Services;

use App\Models\Job;
use App\Services\JobSafetyService;
use Illuminate\Support\Facades\Log;

/**
 * M5 — Fake Vacancy Detection Service
 *
 * Rule-based auto-screening that runs when a job is submitted for review.
 * This is DIFFERENT from JobSafetyService (which is AI-based, worker-facing M10).
 *
 * This service:
 * - Runs server-side automatically when employer submits a job
 * - Produces a risk_level stored in the job_listings table
 * - Produces red_flags and missing_fields stored in job_listings table
 * - Is used by admin panel to triage the review queue
 *
 * JobSafetyService (M10) = AI-based, on-demand, worker-facing
 * JobScreeningService (M5) = Rule-based, automatic, admin-facing
 */
class JobScreeningService
{
    /**
     * Screen a job and return the result array.
     * Does NOT save to DB — caller is responsible for persisting.
     */
    public function screen(Job $job): array
    {
        $redFlags      = [];
        $missingFields = [];

        // ── CRITICAL flags (auto-reject candidates) ──────────────────────────
        if (empty($job->salary)) {
            $redFlags[] = 'Salary is missing.';
        }

        if (empty($job->location)) {
            $redFlags[] = 'Work location is not specified.';
        }

        if (empty($job->eligibility) || strtolower($job->eligibility) === 'unknown') {
            $redFlags[] = 'Eligibility requirement is Unknown or not specified.';
        }

        if ($job->employer_type === 'agency' && empty($job->employer_authorization_url)) {
            $redFlags[] = 'Agency job is missing employer authorization document.';
        }

        // ── HIGH-RISK flags ───────────────────────────────────────────────────
        $isLiveInType = in_array($job->employer_type, ['factory', 'family_care']);

        if ($isLiveInType && empty($job->working_hours_and_rest_days)) {
            $redFlags[] = 'Working hours and rest days not specified for ' . $job->employer_type . ' job.';
        }

        if ($isLiveInType && empty($job->dormitory_meals_deductions)) {
            $redFlags[] = 'Dormitory / meals / deductions info is missing for a live-in type job.';
        }

        if (empty($job->contact_method) && !$job->mask_contact_info) {
            $redFlags[] = 'No contact method provided and contact is not masked through platform.';
        }

        // Salary too low to be legitimate (< NT$10,000 for monthly)
        if (!empty($job->salary) && !empty($job->salary_period) && $job->salary_period === 'Month') {
            $numericSalary = (float) preg_replace('/[^0-9.]/', '', $job->salary);
            if ($numericSalary > 0 && $numericSalary < 10000) {
                $redFlags[] = 'Monthly salary appears too low (< NT$10,000). Possible data entry error or exploitation risk.';
            }
        }

        // ── MEDIUM-RISK flags ─────────────────────────────────────────────────
        if (strlen((string) $job->description) < 50) {
            $redFlags[] = 'Job description is too short (less than 50 characters).';
        }

        if (empty($job->duties)) {
            $redFlags[] = 'Job duties field is empty.';
        }

        if ($job->employer_type === 'agency' && empty($job->job_source_proof_url)) {
            $redFlags[] = 'Agency job has no job source / proof document uploaded.';
        }

        // ── Missing required fields (informational) ───────────────────────────
        if (empty($job->hours)) {
            $missingFields[] = 'Working hours';
        }
        if (empty($job->employment_type)) {
            $missingFields[] = 'Employment type';
        }
        if (empty($job->category)) {
            $missingFields[] = 'Job category';
        }
        if (empty($job->language)) {
            $missingFields[] = 'Language requirement';
        }
        if (empty($job->requirements)) {
            $missingFields[] = 'Worker requirements';
        }

        // ── Determine risk level ──────────────────────────────────────────────
        $criticalKeywords = [
            'Salary is missing',
            'Work location is not specified',
            'Eligibility requirement is Unknown',
            'Agency job is missing employer authorization',
        ];

        // ── AI-Based Safety Check ─────────────────────────────────────────────
        $aiRiskLevel = 'low';
        try {
            $aiResult = app(JobSafetyService::class)->analyzeJob($job);
            if ($aiResult) {
                $aiRiskLevel = $aiResult['risk_level'] ?? 'low';
                if (in_array($aiRiskLevel, ['high', 'critical'])) {
                    $aiReason = 'AI Risk Analysis (' . ucfirst($aiRiskLevel) . '): ' . implode('; ', $aiResult['risk_reasons'] ?? ['Possible scam or illegal wording detected']);
                    array_unshift($redFlags, $aiReason); // Put AI warning at the top
                    
                    if ($aiRiskLevel === 'critical') {
                        $criticalKeywords[] = 'AI Risk Analysis (Critical)';
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('AI Screening Failed: ' . $e->getMessage());
        }

        $hasCriticalFlag = collect($redFlags)->contains(
            fn($flag) => collect($criticalKeywords)->contains(fn($kw) => str_contains($flag, $kw))
        );

        $riskLevel = match (true) {
            $aiRiskLevel === 'critical' => 'critical', // Force critical if AI flags it
            $hasCriticalFlag && count($redFlags) >= 2 => 'critical',
            $aiRiskLevel === 'high' => 'high',
            $hasCriticalFlag || count($redFlags) >= 3 => 'high',
            count($redFlags) >= 1 || count($missingFields) >= 2 => 'medium',
            default => 'low',
        };

        $requiresManualReview = in_array($riskLevel, ['high', 'critical'])
            || in_array($job->employer_type, ['family_care', 'factory', 'agency']);

        return [
            'risk_level'            => $riskLevel,
            'red_flags'             => $redFlags,
            'missing_fields'        => $missingFields,
            'requires_manual_review'=> $requiresManualReview,
            'auto_rejected'         => $riskLevel === 'critical',
            'screened_at'           => now()->toIso8601String(),
        ];
    }

    /**
     * Screen and persist risk data back to the job model.
     * Returns the screening result array.
     */
    public function screenAndSave(Job $job): array
    {
        $result = $this->screen($job);

        $job->update([
            'risk_level'     => $result['risk_level'],
            'red_flags'      => $result['red_flags'],
            'missing_fields' => $result['missing_fields'],
            'screened_at'    => now(),
            // Auto-reject critical jobs
            'status'         => $result['auto_rejected'] ? 'rejected' : $job->status,
            'rejection_reason' => $result['auto_rejected']
                ? 'Auto-rejected: Critical risk flags detected. ' . implode(' ', array_slice($result['red_flags'], 0, 2))
                : $job->rejection_reason,
        ]);

        return $result;
    }

    /**
     * Get a human-readable label for a risk level.
     */
    public static function riskLabel(string $level): string
    {
        return match ($level) {
            'critical' => 'Critical',
            'high'     => 'High Risk',
            'medium'   => 'Medium Risk',
            default    => 'Low Risk',
        };
    }

    /**
     * Get Bootstrap color class for a risk level.
     */
    public static function riskColor(string $level): string
    {
        return match ($level) {
            'critical' => 'danger',
            'high'     => 'warning',
            'medium'   => 'info',
            default    => 'success',
        };
    }
}
