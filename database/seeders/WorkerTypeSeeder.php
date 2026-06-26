<?php

namespace Database\Seeders;

use App\Models\WorkerType;
use Illuminate\Database\Seeder;

class WorkerTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Update old slug if exists to prevent unique constraint failures
        WorkerType::where('slug', 'aprc_gold_card')->update(['slug' => 'aprc']);

        $types = [
            [
                'worker_type_name' => 'Student ARC',
                'slug'             => 'student',
                'description'      => 'International student with valid ARC — permitted to work part-time (up to 20 hrs/week)',
                'requires_arc'     => true,
                'auto_ready_to_work' => false,
                'eligible_to_work' => true,
            ],
            [
                'worker_type_name' => 'Blue Collar ARC',
                'slug'             => 'blue_collar',
                'description'      => 'Migrant worker with employer-sponsored ARC (factory, care, construction)',
                'requires_arc'     => true,
                'auto_ready_to_work' => false,
                'eligible_to_work' => true,
            ],
            [
                'worker_type_name' => 'White Collar ARC',
                'slug'             => 'white_collar',
                'description'      => 'Professional/white collar worker — requires employer sponsorship',
                'requires_arc'     => true,
                'auto_ready_to_work' => false,
                'eligible_to_work' => true,
            ],
            [
                'worker_type_name' => 'ARC Other',
                'slug'             => 'arc_other',
                'description'      => 'Worker with open work rights ARC (spouse of citizen, JFRV, etc.)',
                'requires_arc'     => true,
                'auto_ready_to_work' => true,
                'eligible_to_work' => true,
            ],
            [
                'worker_type_name' => 'APRC / Gold Card',
                'slug'             => 'aprc',
                'description'      => 'Permanent resident or Gold Card holder — full open work rights',
                'requires_arc'     => false,
                'auto_ready_to_work' => true,
                'eligible_to_work' => true,
            ],
            [
                'worker_type_name' => 'Taiwanese',
                'slug'             => 'taiwanese',
                'description'      => 'ROC National — no ARC or work permit required',
                'requires_arc'     => false,
                'auto_ready_to_work' => true,
                'eligible_to_work' => true,
            ],
            [
                'worker_type_name' => 'Employment Gold Card',
                'slug'             => 'gold_card',
                'description'      => 'Employment Gold Card holder — full open work rights',
                'requires_arc'     => false,
                'auto_ready_to_work' => true,
                'eligible_to_work' => true,
            ],
            [
                'worker_type_name' => 'Spouse of ROC Citizen',
                'slug'             => 'spouse_roc',
                'description'      => 'Spouse of ROC Citizen (JFRV ARC) — full open work rights',
                'requires_arc'     => true,
                'auto_ready_to_work' => true,
                'eligible_to_work' => true,
            ],
            [
                'worker_type_name' => 'Not Sure',
                'slug'             => 'not_sure',
                'description'      => 'Status unknown — cannot search for work until resolved',
                'requires_arc'     => false,
                'auto_ready_to_work' => false,
                'eligible_to_work' => false,
            ],
            [
                'worker_type_name' => 'Other',
                'slug'             => 'other',
                'description'      => 'Other visa status — admin review required',
                'requires_arc'     => false,
                'auto_ready_to_work' => false,
                'eligible_to_work' => true,
            ],
        ];

        foreach ($types as $type) {
            WorkerType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
