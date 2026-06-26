<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
print_r(DB::table('personal_access_tokens')->get()->toArray());
foreach (App\Models\User::all() as $user) {
    if ($user->tokens()->count() > 0) {
        echo "User ID {$user->id} ({$user->email}, role: {$user->role}) has tokens:\n";
        foreach ($user->tokens as $token) {
            echo "  - Token ID {$token->id}, last used at: {$token->last_used_at}\n";
        }
    }
}

