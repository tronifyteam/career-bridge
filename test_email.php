<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobStatusUpdatedMail;

// Create dummy employer (without saving to DB)
$employer = new User([
    'name' => 'Test Employer',
    'role' => 'family_care',
    'email' => 'test_employer@example.com'
]);

// Create dummy job (without saving to DB)
$job = new Job([
    'title' => 'Test Job for Email',
    'description' => 'Test',
    'requirements' => 'Test',
    'salary' => 45000,
    'status' => 'published',
    'eligibility' => 'All'
]);
// We need to fake the relationship or just not save
$job->setRelation('employer', $employer);

echo "Dispatching Email for Job {$job->title} to {$employer->email}...\n";

try {
    Mail::to($employer->email)->send(new JobStatusUpdatedMail($job));
    echo "Mail logged successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
