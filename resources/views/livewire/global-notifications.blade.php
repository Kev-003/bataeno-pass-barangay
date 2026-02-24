<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
        class="relative p-2 text-gray-400 hover:text-gray-600 focus:outline-none transition group">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        @if($unreadCount > 0)
            <span class="absolute top-2 right-2 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span
                    class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white font-bold items-center justify-center">
                    {{ $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden"
        style="display: none;">

        <div class="px-4 py-3 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-sm font-bold text-gray-800">Notifications</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Mark all as
                    read</button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div class="p-4 border-b border-gray-50 hover:bg-gray-50 transition cursor-pointer {{ $notification->read_at ? 'opacity-60' : 'bg-blue-50/20' }}"
                    wire:click="markAsRead('{{ $notification->id }}')">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            @if(($notification->data['type'] ?? '') === 'document_request')
                                <div class="bg-blue-100 p-1.5 rounded-lg text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @else
                                <div class="bg-emerald-100 p-1.5 rounded-lg text-emerald-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-900">{{ $notification->data['title'] ?? 'Update' }}</p>
                            <p class="text-[11px] text-gray-600 mt-0.5">{{ $notification->data['body'] ?? '' }}</p>
                            <p class="text-[9px] text-gray-400 mt-1 uppercase tracking-tight">
                                {{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <p class="text-xs text-gray-500">No notifications yet.</p>
                </div>
            @endforelse
        </div>

        <a href="#"
            class="block py-3 text-center text-xs font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 transition border-t border-gray-100">
            View all notifications
        </a>
    </div>
</div>