<?php
// The auth signature from the Flutter log
$targetSig = '627db104a75983b77366c64de79e6332dcbedef6f87466ec418fd3d975c92593';
$socketId = '590860208.72694154';
$channel = 'private-chat.30';
$str = $socketId . ':' . $channel;

// Try known secrets to find which one produced the signature
$candidates = [
    'isffjphpw1hghbzajlzh',      // current .env
    'grzrgdnck7ury7qpymzh',      // reverb APP_KEY (sometimes used as secret)
    'secret',
    '',
    '585358',                     // APP_ID
];

echo "Message to sign: $str\n\n";

foreach ($candidates as $s) {
    $sig = hash_hmac('sha256', $str, $s);
    $match = $sig === $targetSig ? ' ✅ MATCH!' : '';
    echo "Secret '$s' -> $sig$match\n";
}

// Also show what the reverb APP config says via artisan
echo "\n--- Running via Laravel config ---\n";
// Quick check via artisan about
