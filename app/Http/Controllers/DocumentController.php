<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GovernanceService;
use App\Models\DocumentTransaction;
use App\Models\DocumentTypeProperty;
use App\Models\Barangay;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    // Signing and Requesting 
    public function request(Request $request)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_type_properties,id',
            'request_origin' => 'required|string',
            'requester_id' => 'nullable|exists:users,id', // Allow officials to specify requester for walk-ins
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

        // Determine the requester
        // If requester_id is provided (walk-in scenario), use it
        // Otherwise, use the authenticated user (self-service)
        $requesterId = $validated['requester_id'] ?? auth()->id();

        // Create Transaction
        // We rely on the User model to set the correct Barangay ID context.
        $transaction = DocumentTransaction::create([
            'requester_id' => $requesterId,
            'barangay_code' => Barangay::where('barangay_code', Barangay::normalizeCode(auth()->user()->barangay_code))->value('id'),
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

    public function sign(Request $request, $barangay_code, $id, GovernanceService $service)
    {
        return DB::transaction(function () use ($request, $barangay_code, $id, $service) {
            $user = $request->user();

            // Resolve Barangay ID from PSGC code (normalize to 9rd-digit if needed)
            $barangayId = Barangay::where('barangay_code', Barangay::normalizeCode($barangay_code))->value('id');

            // Fetch with a Row Lock
            // This prevents other requests from reading/modifying this specific row 
            // until this transaction completes.
            $transaction = DocumentTransaction::where('barangay_code', $barangayId)
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
