<div x-data="{ 
    mode: 'esign',
    init() {
        this.$watch('mode', value => {
            $wire.set('mountedActionsData.0.signature_mode', value)
        })
    }
}" class="mt-4">
    <div x-init="console.log(JSON.stringify($wire.mountedActionsData))">debug</div>

    <div class="flex gap-3">
        <button type="button" x-on:click="mode = 'esign'"
            x-bind:class="mode === 'esign'
                ? 'border-primary-600 bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 ring-1 ring-primary-600'
                : 'border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/20'"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold border-2 transition-all duration-200 shadow-sm">

            <svg x-bind:class="mode === 'esign' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'"
                class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <span>E-Sign</span>
        </button>

        <button type="button" x-on:click="mode = 'ink'"
            x-bind:class="mode === 'ink'
                ? 'border-primary-600 bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 ring-1 ring-primary-600'
                : 'border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/20'"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold border-2 transition-all duration-200 shadow-sm">

            <svg x-bind:class="mode === 'ink' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400'"
                class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Ink Sign</span>
        </button>
    </div>
</div>