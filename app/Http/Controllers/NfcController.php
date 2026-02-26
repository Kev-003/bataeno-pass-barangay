<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Log;

class NfcController extends Controller
{
    /**
     * Simple stub used by an API route reference. Existing apps
     * may provide a real implementation; this stub prevents
     * route registration from failing when the class is missing.
     */
    public function lookup(Request $request)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    // Store latest resident info posted by an external NFC client
    public function setLatest(Request $request)
    {
        $data = $request->all();
        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 422);
        }
        // cache for 60 seconds by default
        Cache::put('nfc.latest', $data, 60);
        Log::info('NFC latest set', ['uid' => $data['uid'] ?? null]);
        return response()->json(['message' => 'ok']);
    }

    // Return latest cached resident (or 204)
    public function latest()
    {
        $val = Cache::get('nfc.latest');
        if (! $val) return response()->noContent();
        return response()->json($val);
    }
}
