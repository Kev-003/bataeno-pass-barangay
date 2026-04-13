<div class="flex items-center gap-x-1 px-2 py-1 bg-slate-100 rounded-full border border-slate-200">
    <span class="text-[9px] uppercase tracking-tighter font-bold text-slate-500 mx-1">Demo:</span>
    
    <!-- Resident Link -->
    <a href="{{ route('demo.resident') }}" 
       class="px-2 py-0.5 text-[10px] rounded-md transition-all {{ request()->routeIs('dashboard') || request()->routeIs('documents') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}"
       title="Resident Dashboard">
        RES
    </a>
    
    <!-- Official Link -->
    <a href="{{ route('demo.official') }}" 
       class="px-2 py-0.5 text-[10px] rounded-md transition-all {{ request()->routeIs('official.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}"
       title="Barangay Official Panel">
        OFF
    </a>
    
    <!-- City Admin Link -->
    <a href="{{ route('demo.city.admin') }}" 
       class="px-2 py-0.5 text-[10px] rounded-md transition-all {{ request()->routeIs('city.*') ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}"
       title="City Admin Panel">
        CITY
    </a>
    
    <!-- Provincial Admin Link -->
    <a href="{{ route('demo.admin') }}" 
       class="px-2 py-0.5 text-[10px] rounded-md transition-all {{ str_contains(request()->path(), 'admin') && !request()->routeIs('city.*') ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}"
       title="Master Admin Panel">
        ADMIN
    </a>
</div>
