<?php
require 'vendor/autoload.php';
(require 'bootstrap/app.php')->boot();

use App\Models\User;

$user = User::where('email', 'russelsantos142@gmail.com')->first();
if ($user) {
    echo "User: " . $user->email . PHP_EOL;
    echo "ID: " . $user->id . PHP_EOL;
    echo "Permissions: " . implode(', ', $user->getPermissionNames()->toArray()) . PHP_EOL;
    echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}
