<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GovernanceService;
use App\Models\DocumentTransaction;
use App\Models\DocumentTypeProperty;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    // Signing and Requesting 
    public function request(Request $request)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_type_properties,id',
            'request_origin' => 'required|string',
        ]);

        // Rule Integration Check
        // Ensure the requested document type has configured requirements (rules).
        // This validates against the pivot table `document_rules`.
        $docType = DocumentTypeProperty::with('requirements')->find($validated['document_type_id']);

        if ($docType->requirements->isEmpty()) {
            return response()->json([
                'message' => 'This document type has no active requirements configured and cannot be requested.'
            ], 422);
        }

        // Create Transaction
        // We rely on the User model to set the correct Barangay ID context.
        $transaction = DocumentTransaction::create([
            'requester_id' => auth()->id(),
            'barangay_id' => auth()->user()->getActiveBarangayId(),
            'document_type_id' => $validated['document_type_id'],
            'request_origin' => $validated['request_origin'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Request submitted successfully.',
            'transaction_id' => $transaction->id,
            'requirements' => $docType->requirements
        ], 201);
    }

    public function sign(Request $request, $barangay_id, $id, GovernanceService $service)
    {
        return DB::transaction(function () use ($request, $barangay_id, $id, $service) {
            $user = $request->user();

            // Fetch with a Row Lock
            // This prevents other requests from reading/modifying this specific row 
            // until this transaction completes.
            $transaction = DocumentTransaction::where('barangay_id', $barangay_id)
                ->lockForUpdate()
                ->findOrFail($id);

            // State Guard (Check first to avoid unnecessary logic)
            if ($transaction->status === 'issued') {
                abort(403, "Document is already issued.");
            }

            // Authority Check via Service
            if (!$service->canSign($user, $id)) {
                abort(403, "You do not have the authority to sign this document.");
            }

            // Execute Signing
            $term = $user->activeTerm;

            $transaction->update([
                'approver_id' => $term->id,
                'status' => 'issued',
                'issued_at' => now(),
                'signing_capacity' => $term->position_type,
                'checksum' => bin2hex(random_bytes(16)),
            ]);

            return response()->json([
                'message' => 'Document signed and issued.',
                'data' => $transaction
            ]);
        });
    }
}
