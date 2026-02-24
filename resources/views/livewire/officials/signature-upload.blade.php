<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Digital Signature') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Upload your official digital signature to be used for document approval.') }}
        </p>
    </header>

    <form wire:submit="save" class="mt-6 space-y-6">
        <div>
            @if ($currentSignature)
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Current Signature:</p>
                    <div class="p-4 border rounded-xl bg-gray-50 inline-block">
                        <img src="{{ $currentSignature }}" alt="Current Signature" class="max-h-24">
                    </div>
                </div>
            @endif

            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('Upload New Signature') }}
            </label>

            <div class="flex items-center gap-4">
                <input type="file" wire:model="signature" class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100 transition">

                <div wire:loading wire:target="signature" class="text-sm text-blue-600 animate-pulse">
                    Uploading...
                </div>
            </div>

            @error('signature') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

            @if ($signature)
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                    <div class="p-4 border rounded-xl bg-blue-50/30 inline-block">
                        <img src="{{ $signature->temporaryUrl() }}" class="max-h-24">
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition disabled:opacity-50"
                wire:loading.attr="disabled">
                {{ __('Save Signature') }}
            </button>

            @if (session('success'))
                <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600">
                    {{ session('success') }}
                </p>
            @endif

            @if (session('error'))
                <p class="text-sm text-red-600">
                    {{ session('error') }}
                </p>
            @endif
        </div>
    </form>
</section>