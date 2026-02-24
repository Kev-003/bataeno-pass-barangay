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
use Illuminate\Support\Facades\Broadcast;

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

        $transaction = $service->createRequest(
            user: auth()->user(),
            docTypeId: $validated['document_type'],
            modelClass: $metadata->doc_type_model,
            purpose: $validated['purpose'],
            dynamicFields: $request->input('dynamic_fields')
        );

        // Notify barangay officials in real-time via Reverb
        if ($transaction) {
            DocumentRequestCreated::dispatch($transaction);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Your request has been submitted to your Barangay.');
    }


}
