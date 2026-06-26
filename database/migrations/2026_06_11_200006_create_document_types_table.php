<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('document_type_name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            // Which worker_type this doc is required for (null = applies to all)
            $table->foreignId('worker_type_id')->nullable()->constrained('worker_types')->nullOnDelete();
            $table->boolean('required_for_verified_badge')->default(false);
            $table->boolean('required_for_ready_to_work')->default(false);
            $table->boolean('verification_required')->default(true); // admin must review
            $table->timestamps();
        });

        // Seed document types using DB::table to handle boolean correctly on both MySQL & PostgreSQL
        $studentId    = DB::table('worker_types')->where('slug', 'student')->value('id');
        $blueCollarId = DB::table('worker_types')->where('slug', 'blue_collar')->value('id');
        $whiteCollarId= DB::table('worker_types')->where('slug', 'white_collar')->value('id');
        $now          = now();

        DB::table('document_types')->insert([
            // ── Common (all workers) ───────────────────────────────────────
            ['document_type_name' => 'Personal ID / Passport',     'slug' => 'personal_id',          'description' => 'National ID or passport copy',                                  'worker_type_id' => null,          'required_for_verified_badge' => true,  'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Selfie Photo',               'slug' => 'selfie',               'description' => 'Clear selfie photo for identity verification',                   'worker_type_id' => null,          'required_for_verified_badge' => true,  'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            // ── Student ───────────────────────────────────────────────────
            ['document_type_name' => 'Student Work Permit',        'slug' => 'student_work_permit',  'description' => 'Part-time work permit for student ARC holders',                 'worker_type_id' => $studentId,    'required_for_verified_badge' => false, 'required_for_ready_to_work' => true,  'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Enrollment Proof',           'slug' => 'enrollment_proof',     'description' => 'Proof of enrollment from school or university',                  'worker_type_id' => $studentId,    'required_for_verified_badge' => false, 'required_for_ready_to_work' => true,  'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            // ── Blue Collar ───────────────────────────────────────────────
            ['document_type_name' => 'Transfer Document',          'slug' => 'transfer_document',    'description' => 'Transfer document for blue collar workers',                      'worker_type_id' => $blueCollarId, 'required_for_verified_badge' => false, 'required_for_ready_to_work' => true,  'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Work Contract',              'slug' => 'work_contract',        'description' => 'Signed employment contract',                                     'worker_type_id' => $blueCollarId, 'required_for_verified_badge' => false, 'required_for_ready_to_work' => true,  'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Contract Ending Proof',      'slug' => 'contract_ending_proof','description' => 'Proof of previous contract ending date',                         'worker_type_id' => $blueCollarId, 'required_for_verified_badge' => false, 'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            // ── White Collar ──────────────────────────────────────────────
            ['document_type_name' => 'CV / Resume',                'slug' => 'cv',                   'description' => 'Curriculum Vitae or Resume',                                     'worker_type_id' => $whiteCollarId,'required_for_verified_badge' => false, 'required_for_ready_to_work' => true,  'verification_required' => false, 'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Diploma / Certificate',      'slug' => 'diploma',              'description' => 'Highest academic diploma or professional certificate',            'worker_type_id' => $whiteCollarId,'required_for_verified_badge' => false, 'required_for_ready_to_work' => true,  'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Work Permit',                'slug' => 'work_permit',          'description' => 'Work permit for white collar foreign workers',                   'worker_type_id' => $whiteCollarId,'required_for_verified_badge' => false, 'required_for_ready_to_work' => true,  'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            // ── Employer ─────────────────────────────────────────────────
            ['document_type_name' => 'Business Registration',      'slug' => 'business_registration','description' => 'Company registration certificate (for employers)',                'worker_type_id' => null,          'required_for_verified_badge' => false, 'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Agency License',             'slug' => 'agency_license',       'description' => 'Recruitment agency operating license',                           'worker_type_id' => null,          'required_for_verified_badge' => false, 'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Family Employer Identity',   'slug' => 'family_employer_id',   'description' => 'Identity document for family/household employer (看護工)',        'worker_type_id' => null,          'required_for_verified_badge' => false, 'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Agency Staff Card',          'slug' => 'agency_staff_card',    'description' => 'Business card or agency authorization letter for staff (業務)',  'worker_type_id' => null,          'required_for_verified_badge' => false, 'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            // ── Taiwanese (no extra doc for MVP) ──────────────────────────
            ['document_type_name' => 'National ID',                'slug' => 'national_id',          'description' => 'ROC National ID card for Taiwanese citizens',                    'worker_type_id' => null,          'required_for_verified_badge' => false, 'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
            ['document_type_name' => 'Identity Proof',             'slug' => 'identity_proof',       'description' => 'Alternative identity document for other ARC types',             'worker_type_id' => null,          'required_for_verified_badge' => false, 'required_for_ready_to_work' => false, 'verification_required' => true,  'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
