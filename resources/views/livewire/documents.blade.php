<div>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Documents') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sm:p-8 lg:p-12">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Barangay Documents</h1>
                    <a href="{{ route('document.request') }}" class="btn btn-primary">Request New Document</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($this->transactions as $transaction)
                        <livewire:document-cards :transaction="$transaction" :key="$transaction->id" />
                    @empty
                        <div class="col-span-full text-center py-12">
                            <x-heroicon-o-document-plus class="w-12 h-12 text-gray-300 mx-auto" />
                            <p class="mt-2 text-gray-500">No documents found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>