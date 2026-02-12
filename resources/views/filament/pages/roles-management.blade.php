<x-filament-panels::page>
    <div class="gap-y-50">
        <div class="mb-10">
            <div class="mb-4">
                <h2 class="text-xl font-bold tracking-tight">Barangay Officials</h2>
                <p class="text-gray-500">Manage high-level administrative permissions.</p>
            </div>
            @livewire('official-roles')
        </div>
        <div class="mt-10">
            <div class="mb-4">
                <h2 class="text-xl font-bold tracking-tight">Community Roles</h2>
                <p class="text-gray-500">View access levels for residents and heads of households.</p>
            </div>
            @livewire('resident-roles')
        </div>
    </div>
</x-filament-panels::page>