<div>
    <x-slot:title>Official Dashboard</x-slot:title>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold mb-4">Official Dashboard</h1>
                <div class="grid grid-cols-3 gap-4">
                    @foreach($stats as $stat)
                        <livewire:officials.stats-card :title="$stat['title']" :value="$stat['value']"
                            :color="$stat['color']" />
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Document Requests</h3>
                    </div>

                    @forelse($transactions as $transaction)
                        <div class="p-4 mb-3 bg-white border rounded-lg shadow-sm flex justify-between items-center">
                            <div>
                                <span class="font-bold text-blue-600">
                                    {{ $transaction->documentTypeProperty->name ?? 'Unknown Document' }}
                                </span>
                                <span class="text-gray-600">request from</span>
                                <span class="font-bold text-gray-800">
                                    {{ $transaction->requester->name ?? 'Unknown Resident' }}
                                </span>

                                <div class="flex items-center gap-2 mt-1">
                                    <small class="text-gray-500">{{ $transaction->created_at->diffForHumans() }}</small>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] uppercase font-bold 
                                                                {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $transaction->status }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('official.document-approval-process', ['barangay_code' => $psgc_code, 'id' => $transaction->id]) }}"
                                    wire:navigate
                                    class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200 transition">
                                    Details
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 border-2 border-dashed border-gray-200 rounded-lg">
                            <p class="text-gray-500 italic">No document requests found for this Barangay.</p>
                        </div>
                    @endforelse

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>