@extends('livewire.documents.layouts.letterhead')

@section('title', 'Clearance')

@section('content')
    {{-- Flex Container for Sidebar and Content --}}
    <div class="flex border-t-2 border-black mt-4" style="min-height: 900px;">

        {{-- LEFT COLUMN: Officials List --}}
        <div class="w-1/4 border-r-2 border-black p-4 text-center">
            <div class="space-y-6">
                {{-- Dynamic Loop --}}
                @isset($officials)
                    @foreach($officials as $off)
                        <div class="mb-4">
                            <p class="text-xs font-bold uppercase mb-0" style="line-height: 1.1;">
                                {{ $off->user->name }}
                            </p>
                            <p class="text-[9pt] italic text-gray-700">
                                {{ $off->position->name == 'Captain' ? 'Punong Barangay' : $off->position->name }}
                            </p>
                        </div>
                    @endforeach
                @endisset

                {{-- Placeholder Councilors --}}
                <div class="pt-2 border-t border-gray-200">
                    <p class="text-[9pt] font-bold underline mb-2">SANGGUNIANG BARANGAY</p>
                    <ul class="text-[9pt] space-y-1 uppercase list-none p-0">
                        <li>Russel Santos</li>
                        <li>Hiel Shadi Pascual</li>
                        <li>JC Gab Manuel</li>
                        <li>Juan Dela Cruz</li>
                        <li>Maria Clara</li>
                    </ul>
                </div>

                {{-- Secretary/Treasurer Placeholders --}}
                <div class="pt-4 space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase mb-0">Jane Doe</p>
                        <p class="text-[9pt] italic text-gray-700">Barangay Secretary</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase mb-0">John Smith</p>
                        <p class="text-[9pt] italic text-gray-700">Barangay Treasurer</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Document Content --}}
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

            <p class="mb-6 uppercase"><strong>To Whom It May Concern:</strong></p>

            <div class="leading-relaxed text-justify space-y-2" style="font-size: 12pt;">
                <p>
                    This is to certify that <strong>{{ $resident->name ?? "KEVERN ANGELES" }}</strong>, of legal age,
                    {{ $resident->civil_status ?? "SINGLE" }}, Filipino citizen, is a resident of
                    {{ $resident->barangay_name ?? "SANTO DOMINGO" }}, {{ $resident->municity_name ?? "ORION" }}.
                </p>

                <p>
                    This clearance is issued upon the request of the interested party for the purpose of
                    obtaining a <strong>{{ $transaction->purpose ?? "Business Registration" }}</strong>.
                </p>

                <p>
                    This is to certify further that the above-named person is of good moral character, law-abiding citizen,
                    and possesses no record of any crime involving moral turpitude on file with this
                    office nor been a member of any subversive organization seeking to overthrow the government.
                </p>

                <p>
                    Issued this {{ now()->format('jS') }} day of {{ now()->format('F, Y') }} at
                    Barangay {{ $barangay->name ?? "SANTO DOMINGO" }}, {{ $resident->municity_name ?? "ORION" }}.
                </p>
            </div>

            {{-- Signature Block --}}
            <div class="mt-20 flex justify-end text-center">
                <div class="w-72">
                    <p class="font-bold uppercase border-b-2 border-black pb-1">
                        {{ $officials->where('position.name', 'Captain')->first()->user->name ?? 'HIEL SHADDAI PASCUAL' }}
                    </p>
                    <p class="text-sm font-bold mt-1">Punong Barangay</p>
                </div>
            </div>

            <div class="bottom-0 flex justify-start flex-col mt-10">
                <p>Community Tax Cert. No.: <span class="underline">{{ $details->community_tax_id ?? 6942067911 }}</span>
                </p>
                <p>Issued on: <span class="underline">{{ $details->issued_on ?? now()->format('F d, Y') }}</span></p>
                <p>Issued at: <span
                        class="underline">{{ $details->issued_at ?? "Barangay " . $barangay->name ?? "SANTO DOMINGO" }},
                        {{ $resident->municity_name ?? "ORION" }}</span></p>
            </div>
        </div>
    </div>
@endsection