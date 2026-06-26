<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_employer_can_create_draft_job()
    {
        $employer = User::create([
            'name' => 'Unverified Employer',
            'email' => 'unverified@employer.com',
            'password' => bcrypt('Password123!'),
            'role' => 'company',
            'verification_status' => 'pending',
            'verified_badge_status' => 'unverified',
        ]);

        $response = $this->actingAs($employer)->postJson('/api/jobs', [
            'title' => 'Draft Job Title',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'status' => 'draft',
            'employment_type' => 'Full-time',
            'working_hours_and_rest_days' => '08:00 - 17:00',
            'contact_method' => 'Line: hr',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('job_listings', [
            'title' => 'Draft Job Title',
            'status' => 'draft',
            'employer_id' => $employer->id,
        ]);
    }

    public function test_unverified_employer_cannot_publish_job_initially()
    {
        $employer = User::create([
            'name' => 'Unverified Employer',
            'email' => 'unverified@employer.com',
            'password' => bcrypt('Password123!'),
            'role' => 'company',
            'verification_status' => 'pending',
            'verified_badge_status' => 'unverified',
        ]);

        // Attempting to publish without status (defaults to publish) or explicit status: published
        $response = $this->actingAs($employer)->postJson('/api/jobs', [
            'title' => 'Published Job Title',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'status' => 'published',
            'employment_type' => 'Full-time',
            'working_hours_and_rest_days' => '08:00 - 17:00',
            'contact_method' => 'Line: hr',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error', 'unverified_employer');
    }

    public function test_verified_employer_can_publish_job()
    {
        $employer = User::create([
            'name' => 'Verified Employer',
            'email' => 'verified@employer.com',
            'password' => bcrypt('Password123!'),
            'role' => 'company',
            'verification_status' => 'basic_verified',
            'verified_badge_status' => 'verified',
        ]);

        $response = $this->actingAs($employer)->postJson('/api/jobs', [
            'title' => 'Published Job Title',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'eligibility' => 'Blue Collar',
            'status' => 'published',
            'employment_type' => 'Full-time',
            'working_hours_and_rest_days' => '08:00 - 17:00',
            'contact_method' => 'Line: hr',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', 'published');
    }

    public function test_unverified_employer_cannot_publish_existing_draft()
    {
        $employer = User::create([
            'name' => 'Unverified Employer',
            'email' => 'unverified@employer.com',
            'password' => bcrypt('Password123!'),
            'role' => 'company',
            'verification_status' => 'pending',
            'verified_badge_status' => 'unverified',
        ]);

        $job = Job::create([
            'employer_id' => $employer->id,
            'employer_name' => $employer->name,
            'employer_type' => $employer->role,
            'title' => 'Existing Draft Job',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'status' => 'draft',
        ]);

        // Trying to publish
        $response = $this->actingAs($employer)->postJson("/api/jobs/{$job->id}/publish");

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error', 'unverified_employer');
    }

    public function test_agency_staff_cannot_publish_if_agency_is_unverified()
    {
        $agency = User::create([
            'name' => 'Unverified Agency',
            'email' => 'agency@unverified.com',
            'password' => bcrypt('Password123!'),
            'role' => 'agency',
            'verification_status' => 'pending',
            'verified_badge_status' => 'unverified',
        ]);

        $staff = User::create([
            'name' => 'Agency Staff',
            'email' => 'staff@agency.com',
            'password' => bcrypt('Password123!'),
            'role' => 'agency_staff',
            'verification_status' => 'pending',
            'verified_badge_status' => 'unverified',
        ]);

        \App\Models\EmployerStaff::create([
            'user_id' => $staff->id,
            'agency_employer_id' => $agency->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($staff)->postJson('/api/jobs', [
            'title' => 'Staff Job Title',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'eligibility' => 'Blue Collar',
            'status' => 'published',
            'employment_type' => 'Full-time',
            'working_hours_and_rest_days' => '08:00 - 17:00',
            'contact_method' => 'Line: hr',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error', 'unverified_employer');
    }

    public function test_agency_staff_can_publish_if_agency_is_verified_and_staff_is_approved()
    {
        $agency = User::create([
            'name' => 'Verified Agency',
            'email' => 'agency@verified.com',
            'password' => bcrypt('Password123!'),
            'role' => 'agency',
            'verification_status' => 'manually_verified',
            'verified_badge_status' => 'verified',
            'license_number' => 'AG-9999',
        ]);

        $staff = User::create([
            'name' => 'Agency Staff',
            'email' => 'staff@agency.com',
            'password' => bcrypt('Password123!'),
            'role' => 'agency_staff',
            'verification_status' => 'pending',
            'verified_badge_status' => 'unverified',
        ]);

        \App\Models\EmployerStaff::create([
            'user_id' => $staff->id,
            'agency_employer_id' => $agency->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($staff)->postJson('/api/jobs', [
            'title' => 'Staff Job Title',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'eligibility' => 'Blue Collar',
            'status' => 'published',
            'employment_type' => 'Full-time',
            'working_hours_and_rest_days' => '08:00 - 17:00',
            'contact_method' => 'Line: hr',
        ]);

        // Since agency is verified and staff is approved, it goes through but since agency role requires manual review for job listings, it gets forced to submitted_for_review
        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', 'submitted_for_review');
    }

    public function test_employer_can_list_own_jobs_with_status_filtering()
    {
        $employer = User::create([
            'name' => 'Verified Employer',
            'email' => 'verified@employer.com',
            'password' => bcrypt('Password123!'),
            'role' => 'company',
            'verification_status' => 'basic_verified',
            'verified_badge_status' => 'verified',
        ]);

        $otherEmployer = User::create([
            'name' => 'Other Employer',
            'email' => 'other@employer.com',
            'password' => bcrypt('Password123!'),
            'role' => 'company',
            'verification_status' => 'basic_verified',
            'verified_badge_status' => 'verified',
        ]);

        // Job 1: Published by this employer
        Job::create([
            'employer_id' => $employer->id,
            'employer_name' => $employer->name,
            'employer_type' => $employer->role,
            'title' => 'Employer Job Published',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'status' => 'published',
        ]);

        // Job 2: Draft by this employer
        Job::create([
            'employer_id' => $employer->id,
            'employer_name' => $employer->name,
            'employer_type' => $employer->role,
            'title' => 'Employer Job Draft',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'status' => 'draft',
        ]);

        // Job 3: Published by other employer
        Job::create([
            'employer_id' => $otherEmployer->id,
            'employer_name' => $otherEmployer->name,
            'employer_type' => $otherEmployer->role,
            'title' => 'Other Employer Job',
            'location' => 'Taipei',
            'salary' => '30000',
            'category' => 'Factory',
            'status' => 'published',
        ]);

        // 1. Get all jobs of this employer
        $response = $this->actingAs($employer)->getJson('/api/employer/jobs');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');

        // 2. Get only draft jobs
        $response = $this->actingAs($employer)->getJson('/api/employer/jobs?status=draft');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.status', 'draft');
        $response->assertJsonPath('data.0.title', 'Employer Job Draft');

        // 3. Get with comma-separated status
        $response = $this->actingAs($employer)->getJson('/api/employer/jobs?status=draft,published');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }
}
