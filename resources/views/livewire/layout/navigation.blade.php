<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center gap-x-4">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-9 fill-current text-gray-800" />
                    </a>
                    @if(request()->routeIs('official.*'))
                        <div class="flex flex-col">
                            <h1 class="text-md font-bold text-blue-600">
                                {{ auth()->user()->barangay_name . ', ' . auth()->user()->municity_name }}
                            </h1>
                            <p class="text-xs text-gray-500">OFFICIAL'S DASHBOARD</p>
                        </div>
                    @endif
                </div>

                <!-- Demo Panel Switcher -->
                <div class="hidden sm:flex items-center ml-4">
                    <x-demo-mode-switcher />
                </div>
                @auth
                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <!-- <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                                                                                                                                                                    {{ __('Dashboard') }}
                                                                                                                                                                </x-nav-link> -->
                        @if(request()->routeIs('official.*'))
                            <x-nav-link :href="route('official.dashboard', ['barangay_code' => auth()->user()->getActiveBarangayCode()])" :active="request()->routeIs('official.dashboard')"
                                wire:navigate>
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('official.residents', ['barangay_code' => auth()->user()->getActiveBarangayCode()])" :active="request()->routeIs('official.residents')"
                                wire:navigate>
                                {{ __('Residents') }}
                            </x-nav-link>
                            <x-nav-link :href="route('official.document-processing', ['barangay_code' => auth()->user()->getActiveBarangayCode()])"
                                :active="request()->routeIs('official.document-processing')" wire:navigate>
                                {{ __('Document Processing') }}
                            </x-nav-link>
                            <x-nav-link :href="route('official.official-management', ['barangay_code' => auth()->user()->getActiveBarangayCode()])"
                                :active="request()->routeIs('official.official-management')" wire:navigate>
                                {{ __('Official Management') }}
                            </x-nav-link>
                        @else
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('documents')" :active="request()->routeIs('documents')" wire:navigate>
                                {{ __('Documents') }}
                            </x-nav-link>
                            <x-nav-link :href="route('household-profiles')" :active="request()->routeIs('household-profiles')"
                                wire:navigate>
                                {{ __('Household Profiles') }}
                            </x-nav-link>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    @livewire('global-notifications')
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div x-data="{{ json_encode(['name' => auth()->user() ? auth()->user()->name : 'Guest']) }}"
                                    x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if(request()->routeIs('official.*'))
                                <x-dropdown-link :href="route('official.profile', ['barangay_code' => auth()->user()->getActiveBarangayCode()])" wire:navigate>
                                    {{ __('Profile') }}
                                </x-dropdown-link>
                            @else
                                <x-dropdown-link :href="route('profile')" wire:navigate>
                                    {{ __('Profile') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('bataeno.login') }}"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm">
                        Login
                    </a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        @auth
            <div class="pt-2 pb-3 space-y-1">
                @if(request()->routeIs('official.*'))
                    <x-responsive-nav-link :href="route('official.dashboard', ['barangay_code' => auth()->user()->getActiveBarangayCode()])" :active="request()->routeIs('official.dashboard')"
                        wire:navigate>
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('official.residents', ['barangay_code' => auth()->user()->getActiveBarangayCode()])" :active="request()->routeIs('official.residents')"
                        wire:navigate>
                        {{ __('Residents') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('official.document-processing', ['barangay_code' => auth()->user()->getActiveBarangayCode()])" :active="request()->routeIs('official.document-processing')"
                        wire:navigate>
                        {{ __('Document Processing') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('official.official-management', ['barangay_code' => auth()->user()->getActiveBarangayCode()])" :active="request()->routeIs('official.official-management')"
                        wire:navigate>
                        {{ __('Official Management') }}
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('documents')" :active="request()->routeIs('documents')" wire:navigate>
                        {{ __('Documents') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('household-profiles')"
                        :active="request()->routeIs('household-profiles')" wire:navigate>
                        {{ __('Household Profiles') }}
                    </x-responsive-nav-link>
                @endif
            </div>


            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="flex justify-between">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800"
                            x-data="{{ json_encode(['name' => auth()->user() ? auth()->user()->name : 'Guest']) }}"
                            x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                        <div class="font-medium text-sm text-gray-500">{{ auth()->user() ? auth()->user()->email : '' }}</div>
                    </div>
                    @livewire('global-notifications')
                </div>

                <div class="mt-3 space-y-1">
                    @if(request()->routeIs('official.*'))
                        <x-responsive-nav-link :href="route('official.profile', ['barangay_code' => auth()->user()->getActiveBarangayCode()])" wire:navigate>
                            {{ __('Profile') }}
                        </x-responsive-nav-link>
                    @else
                        <x-responsive-nav-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-responsive-nav-link>
                    @endif

                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </button>
                </div>
            </div>
        @endauth
    </div>
</nav>