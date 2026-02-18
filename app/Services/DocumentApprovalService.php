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
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($filePath)) {
            return null;
        }

        $fileData = \Illuminate\Support\Facades\Storage::disk('local')->get($filePath);

        $mimeType = \Illuminate\Support\Facades\Storage::disk('local')->mimeType($filePath);

        return 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
    }

    public function generateAndSign(DocumentTransaction $transaction, BarangayTerm $official)
    {
        // Access the PSGC code via the relationship, since the local column 'barangay_code' stores the ID
        $psgcCode = $transaction->barangay->barangay_code;

        $sealPath = "barangay-assets/{$psgcCode}/seal.png";

        // Use the PSGC code for the folder path to be consistent with uploads
        $signaturePath = "barangay-assets/" . $psgcCode . "/signatures/{$official->user_id}.jpg";

        $sealBase64 = $this->getBase64Image($sealPath);
        $signatureBase64 = $this->getBase64Image($signaturePath);

        if (!$sealBase64) {
            throw new \Exception("Official seal not found at path: '{$sealPath}'. Please upload your seal in settings.");
        }
        if (!$signatureBase64) {
            throw new \Exception("Official signature not found at path: '{$signaturePath}'. Please upload your signature in settings.");
        }

        // Load required relationships
        $transaction->load(['barangay.municipalityByCode', 'documentType', 'requester']);

        $slug = str($transaction->documentType->name)->slug();

        $barangay = $transaction->barangay;
        $municipality = $barangay->municipalityByCode;

        $viewData = [
            'transaction' => $transaction,
            'details' => $transaction->getSpecificDetails(),
            'resident' => $transaction->requester,
            'barangay' => $barangay,
            'municipality' => $municipality,
            'seal' => $sealBase64,           // For template
            'signature' => $signatureBase64, // For template
            'barangaySeal' => $sealBase64,  // For layout
            'citySeal' => $sealBase64,      // Fallback for layout
            'official' => $official,
        ];

        // Add helper properties to barangay object for the layout if they don't exist
        $barangay->province = $municipality->province_name ?? 'Bataan';
        $barangay->city = $municipality->name ?? '';
        $barangay->address = "Barangay Hall, {$barangay->name}"; // Default
        $barangay->contact_number = "N/A"; // Default

        // Debug check
        foreach ($viewData as $key => $value) {
            if (is_null($value)) {
                // throw new \Exception("Variable '$key' is null in generateAndSign!");
            }
        }

        $html = view("livewire.documents.templates.{$slug}", $viewData)->render();
        $fileName = "generated/{$transaction->barangay_code}/{$transaction->id}_signed.pdf";
        $fullPath = storage_path("app/{$fileName}");

        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        Browsershot::html($html)
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($fullPath);

        $transaction->update([
            'status' => 'issued',
            'file_path' => $fileName,
            'issued_at' => now(),
            'approver_id' => $official->id,
        ]);

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