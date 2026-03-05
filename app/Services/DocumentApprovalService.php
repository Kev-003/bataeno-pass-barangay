<?php

namespace App\Services;

use App\Models\DocumentTransaction;
use App\Models\User;
use App\Models\BarangayTerm;
use App\Notifications\DocumentRequestReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\DocumentTypeProperty;

use Spatie\Browsershot\Browsershot;
use App\Events\DocumentIssued;
use App\Notifications\DocumentIssuedNotification;
class DocumentApprovalService
{
    public function getTransactionDetails(DocumentTransaction $transaction)
    {
        $transaction->load(['requester', 'documentTypeProperty']);

        $modelClass = $transaction->documentTypeProperty->doc_type_model;

        // Ensure model class is fully qualified if it's just the class name
        if (!str_contains($modelClass, '\\')) {
            $modelClass = "App\\Models\\{$modelClass}";
        }

        $fields = null;
        if (class_exists($modelClass)) {
            $fields = $modelClass::where('transaction_id', $transaction->id)->first();
        }

        return [
            'transaction' => $transaction,
            'fields' => $fields ? $fields->toArray() : [],
            'requester' => $transaction->requester,
            'metadata' => $transaction->documentTypeProperty
        ];
    }

    protected function getBase64Image($filePath)
    {
        // Strip "storage/app/" if it was accidentally passed
        $cleanPath = str_replace('storage/app/', '', $filePath);

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($cleanPath)) {
            return null;
        }

        $fileData = \Illuminate\Support\Facades\Storage::disk('local')->get($cleanPath);
        $mimeType = \Illuminate\Support\Facades\Storage::disk('local')->mimeType($cleanPath);

        return 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
    }

    public function generateAndSign(DocumentTransaction $transaction, BarangayTerm $official)
    {
        // Load Relationships — municipality() FK: barangays.municity_code -> municipalities.id
        $transaction->load(['barangay.municipality', 'documentType', 'requester']);

        $barangay = $transaction->barangay;
        $municipality = $barangay->municipality; // municipalities row

        // Crawl path: barangays.municity_code (int FK) -> municipalities.id -> municipalities.municity_code (PSGC string)
        $psgcCode = $barangay->barangay_code;
        $psgcMunicity = $municipality?->municity_code; // The actual PSGC string on the municipalities table


        $signatureBase64 = null;

        // Use the exact filename generated during Profile upload.
        $signaturePath = $official->user->digital_signature;

        if ($signaturePath && \Illuminate\Support\Facades\Storage::disk('local')->exists($signaturePath)) {
            $signatureBase64 = $this->getBase64Image($signaturePath);
        }

        if (!$signatureBase64) {
            throw new \Exception("Official signature not configured or file missing for ID: {$official->user_id}. Go to Profile to set it up.");
        }

        $slug = str($transaction->documentType->name)->slug();

        // Fetch Seals (Removed "storage/app/" prefix because disk('local') handles it)
        $barangaySealBase64 = $this->getBase64Image("barangay-assets/{$psgcCode}/seal.png");
        $municipalSealBase64 = $this->getBase64Image("municity-assets/{$psgcMunicity}/seal.png");
        $provincialSealBase64 = $this->getBase64Image("provincial-assets/seal.png");

        $officials = BarangayTerm::where('barangay_id', $barangay->id)
            ->where(function ($q) {
                $q->where('ended_at', '>=', now())->orWhereNull('ended_at');
            })
            ->with(['user', 'position'])
            ->get();

        $viewData = [
            'transaction' => $transaction,
            'details' => $transaction->getSpecificDetails(),
            'resident' => $transaction->requester,
            'barangay' => $barangay,
            'municipality' => $municipality,
            'signature' => $signatureBase64,
            'barangaySeal' => $barangaySealBase64,
            'municipalSeal' => $municipalSealBase64,
            'provincialSeal' => $provincialSealBase64,
            'citySeal' => $municipalSealBase64,
            'official' => $official,
            'officials' => $officials,

            // Pass as standalone variables instead
            'province' => $municipality->province_name ?? 'Bataan',
            'city' => $municipality->name ?? '',
            'barangayAddress' => "Barangay Hall, {$barangay->name}",
            'contactNumber' => 'N/A',
        ];
        // // Add helper properties to barangay object for the layout if they don't exist
        // $barangay->province = $municipality->province_name ?? 'Bataan';
        // $barangay->city = $municipality->name ?? '';
        // $barangay->address = "Barangay Hall, {$barangay->name}"; // Default
        // $barangay->contact_number = "N/A"; // Default

        $html = view("livewire.documents.templates.{$slug}", $viewData)->render();

        $docSlug = str($transaction->documentType->name)->slug();
        $fileName = "{$transaction->requester->id}/{$transaction->barangay->barangay_code}/{$docSlug}/{$transaction->id}_signed.pdf";
        $fullPath = \Illuminate\Support\Facades\Storage::disk('documents')->path($fileName);

        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        Browsershot::html($html)
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($fullPath);

        $validityDays = $transaction->documentTypeProperty->validity_days;
        $expiryDate = $validityDays ? now()->addDays($validityDays) : null;

        // Check if authority is delegated
        $delegation = \App\Models\Delegation::where('delegate_term_id', $official->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        $transaction->update([
            'status' => 'issued',
            'file_path' => $fileName,
            'issued_at' => now(),
            'expiry_date' => $expiryDate,
            'approver_id' => $official->id,

            //if delegated
            'on_behalf_of' => $delegation ? $delegation->granter_term_id : $official->id,
        ]);

        // Notify the resident in real-time via Reverb
        DocumentIssued::dispatch($transaction);

        // Save to notification history
        $transaction->requester->notify(new DocumentIssuedNotification($transaction));

        return $fullPath;
    }

    // protected function notifyUser($transaction)
    // {
    //     $user = User::officialsForBarangay($transaction->barangay_id)->get();

    //     if ($user->count() > 0) {
    //         Notification::send($user, new DocumentRequestReceived($transaction));
    //     }
    // }


}

// protected function notifyUser($transaction)
// {
//     $user = User::officialsForBarangay($transaction->barangay_id)->get();

//     if ($user->count() > 0) {
//         Notification::send($user, new DocumentRequestReceived($transaction));
//     }
// }


