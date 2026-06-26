<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\WorkerType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            // ================= STUDENT =================
            [
                'document_type_name'          => 'Student Work Permit',
                'slug'                        => 'student_work_permit',
                'description'                 => 'Ministry of Education / NIA issued student part-time work permit',
                'worker_type_id'              => null, // mapped below
                'required_for_verified_badge' => false,
                'required_for_ready_to_work'  => true,
                'verification_required'       => true,
            ],
            [
                'document_type_name'          => 'Proof of Enrollment',
                'slug'                        => 'enrollment_proof',
                'description'                 => 'Current semester school enrollment certificate',
                'worker_type_id'              => null,
                'required_for_verified_badge' => false,
                'required_for_ready_to_work'  => true,
                'verification_required'       => true,
            ],

            // ================= BLUE COLLAR =================
            [
                'document_type_name'          => 'Transfer Document / CDC',
                'slug'                        => 'transfer_document',
                'description'                 => 'Current employer permission for transfer (CDC or equivalent)',
                'worker_type_id'              => null,
                'required_for_verified_badge' => false,
                'required_for_ready_to_work'  => true,
                'verification_required'       => true,
            ],
            [
                'document_type_name'          => 'Work Contract',
                'slug'                        => 'work_contract',
                'description'                 => 'Signed employment contract with current or future employer',
                'worker_type_id'              => null,
                'required_for_verified_badge' => false,
                'required_for_ready_to_work'  => false,
                'verification_required'       => false,
            ],
            [
                'document_type_name'          => 'Contract Ending Proof',
                'slug'                        => 'contract_ending_proof',
                'description'                 => 'Document proving the contract is about to end',
                'worker_type_id'              => null,
                'required_for_verified_badge' => false,
                'required_for_ready_to_work'  => true,
                'verification_required'       => true,
            ],

            // ================= WHITE COLLAR =================
            [
                'document_type_name'          => 'Work Permit',
                'slug'                        => 'work_permit',
                'description'                 => 'Professional work permit issued by Ministry of Labor',
                'worker_type_id'              => null,
                'required_for_verified_badge' => false,
                'required_for_ready_to_work'  => true,
                'verification_required'       => true,
            ],
            [
                'document_type_name'          => 'Diploma / Degree Certificate',
                'slug'                        => 'diploma',
                'description'                 => 'University or vocational graduation certificate',
                'worker_type_id'              => null,
                'required_for_verified_badge' => false,
                'required_for_ready_to_work'  => false,
                'verification_required'       => false,
            ],

            // ================= TAIWANESE =================
            [
                'document_type_name'          => 'National ID Card',
                'slug'                        => 'national_id',
                'description'                 => 'Taiwanese National Identification Card',
                'worker_type_id'              => null,
                'required_for_verified_badge' => true,
                'required_for_ready_to_work'  => false,
                'verification_required'       => true,
            ],

            // ================= OTHER WORKER TYPE =================
            [
                'document_type_name'          => 'Identity Proof',
                'slug'                        => 'identity_proof',
                'description'                 => 'Valid passport or ARC',
                'worker_type_id'              => null,
                'required_for_verified_badge' => true,
                'required_for_ready_to_work'  => false,
                'verification_required'       => true,
            ],

            // ================= EMPLOYERS =================
            [
                'document_type_name'          => 'Business Proof / Tax Registration',
                'slug'                        => 'company_registration',
                'description'                 => 'Proof of business or tax registration for companies',
                'worker_type_id'              => null, // employers dont use worker_type_id
                'required_for_verified_badge' => true,
                'required_for_ready_to_work'  => false,
                'verification_required'       => true,
            ],
            [
                'document_type_name'          => 'Agency License',
                'slug'                        => 'agency_license',
                'description'                 => 'Valid manpower agency license',
                'worker_type_id'              => null,
                'required_for_verified_badge' => true,
                'required_for_ready_to_work'  => false,
                'verification_required'       => true,
            ],
            [
                'document_type_name'          => 'Agency Staff Card',
                'slug'                        => 'agency_staff_card',
                'description'                 => 'Authorization card for agency staff',
                'worker_type_id'              => null,
                'required_for_verified_badge' => true,
                'required_for_ready_to_work'  => false,
                'verification_required'       => true,
            ],
            [
                'document_type_name'          => 'Household Registration',
                'slug'                        => 'family_employer_id',
                'description'                 => 'Household registration or ID for family employers',
                'worker_type_id'              => null,
                'required_for_verified_badge' => true,
                'required_for_ready_to_work'  => false,
                'verification_required'       => true,
            ],
        ];

        // Map slugs to worker_type_ids
        $studentType    = WorkerType::where('slug', 'student')->first();
        $blueCollarType = WorkerType::where('slug', 'blue_collar')->first();
        $whiteCollarType= WorkerType::where('slug', 'white_collar')->first();
        $taiwaneseType  = WorkerType::where('slug', 'taiwanese')->first();
        $otherType      = WorkerType::where('slug', 'other')->first();

        $workerTypeMap = [
            'student_work_permit'   => $studentType?->id,
            'enrollment_proof'      => $studentType?->id,
            
            'transfer_document'     => $blueCollarType?->id,
            'work_contract'         => $blueCollarType?->id,
            'contract_ending_proof' => $blueCollarType?->id,
            
            'work_permit'           => $whiteCollarType?->id,
            'diploma'               => $whiteCollarType?->id,
            
            'national_id'           => $taiwaneseType?->id,
            
            'identity_proof'        => $otherType?->id,
        ];

        // Note: Employer documents remain with worker_type_id = null

        foreach ($documents as $doc) {
            $doc['worker_type_id'] = $workerTypeMap[$doc['slug']] ?? null;
            DocumentType::updateOrCreate(['slug' => $doc['slug']], $doc);
        }
    }
}
