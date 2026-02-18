@extends('livewire.documents.layouts.letterhead')

@section('content')
    {{-- Header with Seal --}}
    <div class="relative header-section">
        {{-- Pass the base64 string we prepared in the Service --}}
        <x-documents.components.seal :image="$seal" />

        <h1>Barangay Clearance</h1>
    </div>

    {{-- Body Content... --}}

    {{-- Footer with Signature --}}
    <div class="mt-12 text-center float-right w-64">
        <x-documents.components.signature :image="$signature" />

        <div class="border-t border-black mt-2 pt-2 font-bold uppercase">
            {{ $official->name }}
        </div>
        <div class="text-sm">Punong Barangay</div>
    </div>
@endsection