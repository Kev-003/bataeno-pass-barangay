<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

function testApi()
{
    // We need a token. We can't access session in a console script easily unless we know the session ID 
    // or we can just query the last token.
    // Wait, the official is logged in, but we are running from CLI.
    // Since we don't have the session, let's grab the token from the cache or DB if they have it?
    // They store it in Session: Session::put('bataeno_access_token', $accessToken);
    // Is there a way to hit the API without the session? No.
    // Let's create an Artisan command and a web route to test it.
}
