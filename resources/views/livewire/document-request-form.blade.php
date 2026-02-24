<form wire:submit.prevent="submit">
    <div class="min-h-screen bg-slate-50 py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-100">

                <div class="p-8 min-h-[400px]">
                    {{-- STEP 1: SELECT --}}
                    @if($step === 1)
                        <div wire:key="step-1" class="animate-in fade-in slide-in-from-right-4 duration-500">
                            <h4 class="text-lg font-bold text-slate-800 mb-4">Available Documents</h4>
                            <div class="grid gap-4">
                                @foreach ($documents as $doc)
                                    <button type="button" wire:click="setDoc({{ $doc->id }})"
                                        class="w-full p-6 text-left border-2 rounded-2xl transition-all duration-200 hover:border-blue-400">
                                        <h5 class="font-bold text-slate-900">{{ $doc->name }}</h5>
                                        <p class="text-xs text-slate-500">{{ $doc->description }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- STEP 2: PURPOSE --}}
                    @if($step === 2)
                        <div wire:key="step-2" class="animate-in fade-in slide-in-from-right-8">
                            <div class="bg-blue-50 p-4 rounded-xl mb-6">
                                <h4 class="font-bold text-blue-900">{{ $selectedDocMetadata['name'] ?? 'Document' }}</h4>
                                <p class="text-xs text-blue-700">Please state your purpose.</p>
                            </div>

                            <div class="space-y-4">
                                <label class="block text-sm font-medium text-slate-700">Purpose of Request</label>
                                <textarea wire:model="purpose" rows="3"
                                    class="w-full rounded-xl border-slate-300"></textarea>
                                @error('purpose') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    {{-- STEP 3: DYNAMIC FIELDS --}}
                    @if($step === 3)
                        <div wire:key="step-3" class="grid grid-cols-1 gap-4 animate-in fade-in slide-in-from-right-8">
                            <h4 class="font-bold text-slate-800">Fill in Details</h4>

                            {{-- FIXED LOOP LOGIC --}}
                            @foreach($dynamicFields as $fieldName => $fieldValue)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $fieldName) }}
                                    </label>

                                    {{-- Bind directly to the array key --}}
                                    <input type="text" wire:model="dynamicFields.{{ $fieldName }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                    @error("dynamicFields.$fieldName")
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- FOOTER NAVIGATION --}}
                <div class="p-8 bg-slate-50/80 border-t border-slate-100 flex justify-between items-center">

                    {{-- Back Button Logic --}}
                    @if($step > 1)
                        <button type="button" wire:click="$set('step', {{ $step - 1 }})"
                            class="text-slate-500 font-bold hover:text-slate-800">
                            Back
                        </button>
                    @else
                        <a href="{{ route('dashboard') }}" class="text-slate-400 font-bold">Cancel</a>
                    @endif

                    {{-- Forward Button Logic --}}
                    <div>
                        @if($step === 1)
                            <button disabled
                                class="px-8 py-3 bg-slate-200 text-slate-400 rounded-xl font-bold cursor-not-allowed">
                                Select Document
                            </button>
                        @elseif($step === 2)
                            {{-- Triggers validation before moving to Step 3 --}}
                            <button type="button" wire:click="validateStep2"
                                class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700">
                                Next
                            </button>
                        @elseif($step === 3)
                            {{-- Submits the form --}}
                            <button type="submit"
                                class="px-8 py-3 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700">
                                <span wire:loading.remove>Submit Request</span>
                                <span wire:loading>Processing...</span>
                            </button>
                            @error('submission')
                                <div class="mt-2 text-red-500 text-xs text-center">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>