<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\DocuTypeSelected;
use Illuminate\Support\Facades\DB;

class ValidateRequirementData
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
    public function handle(DocuTypeSelected $event): void
    {
        $event->requirements = DB::table('document_rules')
            ->join('document_requirements_definitions', 'document_rules.requirement_id', '=', 'document_requirements_definitions.id')
            ->where('document_rules.document_type_id', $event->type) // assuming $event->type is the ID
            ->select(
                'document_requirements_definitions.requirement_name',
                'document_requirements_definitions.data_type',
                'document_requirements_definitions.description'
            )
            ->get()
            ->toArray();

        // $docuTypeStr = strtolower(str_replace(' ', '_', $event->targetModel)) . "s";
        // $event->requirements = DB::table($docuTypeStr)
        //     ->select('*')
        //     ->get()
        //     ->toArray();

        // based on DocuTypeSelected, go to the specific table based on table name
        // $event->targetModel = DB::table($event->targetModel)
        //     ->select('*')
        //     ->toArray();

        // set those fields as requirements (step 3)
        // $event->requirements = array_merge($event->requirements, $event->targetModel);
    }
}
