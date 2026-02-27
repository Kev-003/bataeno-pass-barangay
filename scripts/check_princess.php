<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'princessmarybituaamianit_20101202@1bataan.gov.ph';
$u = User::where('email', $email)->first();

if (! $u) {
    echo "User not found\n";
    exit(0);
}

echo "User id: {$u->id}\n";
$terms = $u->barangayTerms()->get()->toArray();
if (empty($terms)) {
    echo "No barangay terms\n";
} else {
    echo "Barangay terms:\n";
    print_r($terms);
}

$roles = method_exists($u, 'getRoleNames') ? $u->getRoleNames()->toArray() : [];
echo "Roles: " . implode(', ', $roles) . "\n";
