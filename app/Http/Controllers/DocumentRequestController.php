<?php

namespace App\Http\Controllers;

use App\Services\DocumentRequestService;
use Illuminate\Http\Request;
use App\Models\DocumentTransaction;
use Illuminate\Support\Facades\Auth;
use App\Events\DocumentRequestCreated;
use App\Notifications\DocumentRequestReceived;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class DocumentRequestController extends Controller
{
    public function store(Request $request, DocumentRequestService $service)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'document_type' => 'required|exists:document_type_properties,id',
            'purpose' => 'required|min:5',
        ]);

        $metadata = DB::table('document_type_properties')->find($validated['document_type']);

        $service->createRequest(
            user: auth()->user(),                       // The Resident
            docTypeId: $validated['document_type'],     // The ID from the form
            modelClass: $metadata->doc_type_model,      // Extracted from the DB lookup
            purpose: $validated['purpose'],             // The "Why"
            dynamicFields: $request->input('dynamic_fields') // The "Answers"
        );

        // $user->notify(new RequestSubmittedConfirmation($transaction));

        return redirect()->route('dashboard')
            ->with('success', 'Your request has been submitted to your Barangay.');
    }


}
