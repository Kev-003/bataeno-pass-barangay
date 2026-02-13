<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\DocumentRequestCreated;
use Illuminate\Support\Facades\Auth;

class NotifyOfficialOfNewRequest
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DocumentRequestCreated $event): void
    {

    }
}
