<x-filament-panels::page>
    <x-filament-tables::container>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Management') }}
            </h2>
        </x-slot>
        {{ $this->table }}
    </x-filament-tables::container>
</x-filament-panels::page>