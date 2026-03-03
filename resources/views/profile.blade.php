<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                {{ __('Resident Profile') }}
            </h2>
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-mono font-bold">
                ID: {{ auth()->user()->uuid }}
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- TOP SECTION: IDENTITY HEADER --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div x-data="grainient({ color1: '#0a132e', color2: '#121d3d', color3: '#1e3a8a', zoom: 1.1 })"
                    x-ref="container"
                    class="relative overflow-hidden shadow mb-6 sm:p-8 h-32 flex items-center bg-[#0a132e]">
                </div>
                <div class="px-8 pb-8">
                    <div class="relative flex flex-col md:flex-row items-center md:items-end -mt-16 gap-6">
                        <livewire:profile-photo />
                        <div class="flex-1 text-center md:text-left pb-2">
                            <h1 class="text-3xl font-extrabold text-slate-900">{{ auth()->user()->name }}</h1>
                            <p class="text-slate-500 font-medium">
                                {{ auth()->user()->occupation ?? 'No Occupation Listed' }}
                            </p>
                        </div>
                        <div class="flex gap-2 pb-2">
                            <span
                                class="px-4 py-2 bg-slate-100 rounded-xl text-sm font-bold text-slate-700 border border-slate-200">
                                {{ auth()->user()->gender ?? 'Unknown' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- LEFT COLUMN: PERSONAL DETAILS --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <x-heroicon-s-identification class="w-5 h-5 text-blue-600" />
                            Personal Information
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Info Block --}}
                            <x-info-block label="Birth Date" :value="auth()->user()->date_of_birth?->format('F j, Y') ?? 'Unknown'" />
                            <x-info-block label="Family Group" :value="auth()->user()->family?->name ?? 'Not Assigned'" />
                            <x-info-block label="Location" :value="auth()->user()->location" />
                            <x-info-block label="Occupation" :value="auth()->user()->occupation ?? 'N/A'" />

                            <hr class="md:col-span-2 border-slate-100">

                            <x-info-block label="Father" :value="auth()->user()->father?->name ?? 'Unknown'" />
                            <x-info-block label="Mother" :value="auth()->user()->mother?->name ?? 'Unknown'" />
                        </div>
                    </div>

                    {{-- Update Profile Form --}}
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                        <livewire:profile.update-profile-information-form />
                    </div>
                </div>

                {{-- RIGHT COLUMN: SECURITY & ACCOUNT --}}
                <div class="space-y-6">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-6">Security</h3>
                        <livewire:profile.update-password-form />
                    </div>

                    <div class="bg-red-50 p-8 rounded-3xl border border-red-100">
                        <h3 class="text-lg font-bold text-red-800 mb-4">Danger Zone</h3>
                        <livewire:profile.delete-user-form />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>