@extends('livewire.documents.layouts.letterhead')

@section('title', 'Clearance')

@section('content')
    {{-- Flex Container for Sidebar and Content --}}
    <div class="flex border-t-2 border-black mt-4" style="min-height: 900px;">

        {{-- LEFT COLUMN: Officials List (Fixed Width) --}}
        <div class="w-1/4 border-r-2 border-black p-4 text-center">
            <div class="space-y-6">
                {{-- Dynamic Loop --}}
                @isset($officials)
                    @foreach($officials as $off)
                        <div class="official-item">
                            <p class="text-xs font-bold uppercase mb-0" style="line-height: 1.1;">
                                {{ $off->user->name }}
                            </p>
                            <p class="text-[10pt] italic text-gray-700">
                                {{ $off->position->name == 'Captain' ? 'Punong Barangay' : $off->position->name }}
                            </p>
                        </div>
                    @endforeach
                @else
                    <p class="text-xs text-gray-400 italic">Officials loading...</p>
                @endisset

            </div>

            {{-- Bottom Seal Placeholder --}}
            <div class="mt-auto pb-10 opacity-20">
                {{-- <img src="{{ $barangaySeal }}" class="w-24 mx-auto"> --}}
            </div>
        </div>

        {{-- RIGHT COLUMN: Document Content (Flexible Width) --}}
        <div class="w-3/4 p-8">
            <div class="flex flex-col items-end mb-10">
                <div class="flex flex-col items-center">
                    <p class="underline">{{ now()->format('F d, Y') }}</p>
                    <p>DATE</p>
                </div>
            </div>
            <div class="text-center mb-10">
                <h1 style="text-decoration: underline; font-size: 16pt; font-family: serif;" class="font-bold">
                    BARANGAY CLEARANCE
                </h1>
            </div>

            <p class="mb-6 uppercase">
                <strong>To Whom It May Concern:</strong>
            </p>

            <p class="leading-relaxed text-justify space-y-2">
                This is to certify that <strong>{{ strtoupper($resident->name) }}</strong>, of legal
                age,
                {{ strtoupper($resident->civil_status) }}, Filipino citizen, is a resident of
                {{ strtoupper($resident->barangay_name) }},
                {{ strtoupper($resident->municity_name) }}.
            </p>

            <p>
                This clearance is issued upon the request of the interested party for the purpose of
                <strong>{{ $transaction->purpose }}</strong>.
            </p>

            <p>
                This is to certify further that the above-named person is of good moral character, law-abiding citizen,
                and possesses no record of any crime involving moral turpitude on file with this
                office nor been a member of any subversive organization seeking to overthrow the government.
            </p>

            <p>
                Issued this {{ now()->format('jS') }} day of {{ now()->format('F, Y') }} at
                Barangay {{ $barangay->name }}, {{ $resident->municity_name }}.
            </p>

            {{-- Signature Block --}}
            <div class="mt-12 flex justify-end">
                <div class="text-center w-72">
                    <div class="relative mb-0">
                        <x-documents.components.signature :image="$signature" />
                        <div class="border-t border-black mt-2 pt-2 font-bold uppercase">
                            HON. {{ $official->name }}
                        </div>
                    </div>
                    <p class="text-[11pt] font-bold mt-1">Punong Barangay</p>
                </div>
            </div>

            <div class="bottom-0 flex text-[8pt] justify-start flex-col mt-5">
                <p>Community Tax Cert. No.: <span class="underline">{{ $details->community_tax_id }}</span>
                </p>
                <p>Issued on: <span class="underline">{{ $transaction->updated_at->format('F d, Y') }}</span></p>
                <p>Issued at: <span class="underline">{{ "Barangay " . $barangay->name }}</span>
                </p>
            </div>
        </div>
    </div>
@endsection