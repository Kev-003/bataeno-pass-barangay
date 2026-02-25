<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-bold uppercase text-gray-500">Resident</p>
            <p class="text-sm font-medium">{{ $record->requester->name }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase text-gray-500">Document Type</p>
            <p class="text-sm font-medium">{{ $record->documentType->name }}</p>
        </div>
        <div class="col-span-2">
            <p class="text-xs font-bold uppercase text-gray-500">Purpose</p>
            <p class="text-sm font-medium">{{ $record->purpose }}</p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase text-gray-500">Requested At</p>
            <p class="text-sm font-medium">{{ $record->created_at->format('M d, Y h:i A') }}</p>
        </div>
    </div>

    @php
        $details = $record->getSpecificDetails();
        $fields = $details ? collect($details->getAttributes())->except(['id', 'transaction_id', 'created_at', 'updated_at']) : collect();
    @endphp

    @if($fields->isNotEmpty())
        <div class="border-t pt-4">
            <h4 class="text-sm font-bold mb-2">Request Form Data</h4>
            <div class="grid grid-cols-2 gap-y-3 gap-x-4">
                @foreach($fields as $key => $value)
                    @if($value)
                        <div>
                            <p class="text-[10px] font-bold uppercase text-gray-400">{{ str_replace('_', ' ', $key) }}</p>
                            <p class="text-sm font-medium text-blue-600">{{ is_array($value) ? implode(', ', $value) : $value }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>