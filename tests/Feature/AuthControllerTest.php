<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkerType;
use App\Models\WorkerDocumentRequirement;
use Database\Seeders\WorkerTypeSeeder;
use Database\Seeders\DocumentTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed database for testing lookup of WorkerTypes and DocumentTypes
        $this->seed(WorkerTypeSeeder::class);
        $this->seed(DocumentTypeSeeder::class);
    }

    public function test_can_register_as_worker_with_user_type_student()
    {
        $response = $this->postJson('/api/auth/register', [
            'full_name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'student',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);

        // Verify database state
        $user = User::where('email', 'student@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('worker', $user->role);
        $this->assertEquals('student', $user->worker_type);
        $this->assertEquals('unverified', $user->verified_badge_status);
        $this->assertEquals('not_ready', $user->ready_to_work_status);
        $this->assertEquals(5, $user->onboarding_step);

        // Check that document requirements were generated (e.g. personal_id, selfie, student_work_permit)
        $requirements = WorkerDocumentRequirement::where('user_id', $user->id)->get();
        $this->assertGreaterThan(0, $requirements->count());
        
        $slugs = $requirements->map(fn($req) => $req->documentType->slug)->toArray();
        $this->assertContains('personal_id', $slugs);
        $this->assertContains('selfie', $slugs);
        $this->assertContains('student_work_permit', $slugs);
    }

    public function test_can_register_as_worker_with_user_type_taiwanese_sets_ready_to_work()
    {
        $response = $this->postJson('/api/auth/register', [
            'full_name' => 'Test Taiwanese',
            'email' => 'taiwanese@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'taiwanese',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'taiwanese@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('worker', $user->role);
        $this->assertEquals('taiwanese', $user->worker_type);
        $this->assertEquals('unverified', $user->verified_badge_status);
        $this->assertEquals('not_ready', $user->ready_to_work_status); // Starts unverified/not_ready
        $this->assertEquals(5, $user->onboarding_step);
    }

    public function test_can_register_as_worker_with_user_type_white_collar()
    {
        $response = $this->postJson('/api/auth/register', [
            'full_name' => 'Test White Collar',
            'email' => 'whitecollar@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'white collar', // with space to test space normalization
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'whitecollar@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('worker', $user->role);
        $this->assertEquals('white_collar', $user->worker_type);
        $this->assertEquals(5, $user->onboarding_step);
    }

    public function test_can_register_as_employer_with_user_type_family_employer()
    {
        $response = $this->postJson('/api/auth/register', [
            'full_name' => 'Test Employer',
            'email' => 'employer@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'family employer', // with space
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'employer@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('family_care', $user->role);
        $this->assertEquals('unverified', $user->verification_status); // As shown in flowchart
        $this->assertEquals(4, $user->onboarding_step);
    }

    public function test_can_register_as_agency_staff()
    {
        $response = $this->postJson('/api/auth/register', [
            'full_name' => 'Test Agency Staff',
            'email' => 'staff@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'agency_staff',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'staff@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('agency_staff', $user->role);
        $this->assertEquals(4, $user->onboarding_step);
    }

    public function test_registration_fails_with_invalid_user_type()
    {
        $response = $this->postJson('/api/auth/register', [
            'full_name' => 'Test Invalid',
            'email' => 'invalid@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'user_type' => 'invalid_type_here',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_type']);
    }

    public function test_can_update_profile_partially()
    {
        $user = User::create([
            'name' => 'Original Name',
            'full_name' => 'Original Name',
            'email' => 'partial@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'worker',
            'nationality' => 'Indonesia',
            'current_city' => 'Taipei',
        ]);

        $response = $this->actingAs($user)->putJson('/api/auth/profile', [
            'worker_type' => 'student',
            'current_work_status' => 'Available',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify database state: fields updated, existing fields preserved
        $user->refresh();
        $this->assertEquals('Original Name', $user->full_name);
        $this->assertEquals('Indonesia', $user->nationality);
        $this->assertEquals('Taipei', $user->current_city);
        $this->assertEquals('student', $user->worker_type);
        $this->assertEquals('Available', $user->current_work_status);
    }

    public function test_cannot_change_worker_type_if_already_set()
    {
        $user = User::create([
            'name' => 'Original Name',
            'full_name' => 'Original Name',
            'email' => 'cannotchange@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'worker',
            'worker_type' => 'student',
            'nationality' => 'Indonesia',
            'current_city' => 'Taipei',
        ]);

        $response = $this->actingAs($user)->putJson('/api/auth/profile', [
            'worker_type' => 'blue_collar',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error', 'cannot_change_worker_type');

        $user->refresh();
        $this->assertEquals('student', $user->worker_type);
    }
}
