@extends('livewire.documents.layouts.letterhead')

@section('title', 'Business Clearance')

@section('content')
    <div class="text-center mb-10">
        <h1 style="text-decoration: underline; font-size: 16pt; font-family: serif;" class="font-bold">
            BARANGAY CLEARANCE
        </h1>
    </div>

    <p class="mb-6 uppercase">
        <strong>To Whom It May Concern:</strong>
    </p>

    <p style="margin-top: 30px;">
        This is to certify that <strong>{{ $resident->name }}</strong>, of legal age,
        {{ $resident->civil_status }}, Filipino citizen, and a resident of
        {{ $resident->barangay_name }}, {{ $resident->municity_name }}, has applied for a <strong>Business
            Clearance</strong>
        to operate the following business:
    </p>

    <div style="margin: 30px auto; width: 80%; background: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
        <p><strong>Business Name:</strong> {{ $details->business_name }}</p>
        <p><strong>Business Type:</strong> {{ $details->business_type }}</p>
        <p><strong>Location:</strong> {{ $details->location }}</p>
    </div>

    <p>
        This clearance is issued upon the request of the interested party for the purpose of
        obtaining a <strong>{{ $transaction->purpose }}</strong>.
    </p>

    <p style="margin-top: 50px;">
        Issued this {{ now()->format('jS') }} day of {{ now()->format('F, Y') }} at
        Barangay {{ $barangay->name }}.
    </p>

    {{-- Signature Block --}}
    {{-- Footer with Signature --}}
    <div class="mt-12 text-center float-right w-64">
        <x-documents.components.signature :image="$signature" />

        <div class="border-t border-black mt-2 pt-2 font-bold uppercase">
            HON. {{ $official->name }}
        </div>
        <div class="text-sm">Punong Barangay</div>
    </div>
@endsection