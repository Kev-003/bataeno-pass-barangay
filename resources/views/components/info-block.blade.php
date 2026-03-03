@props(['label', 'value'])

<div class="space-y-1">
    <h4 class="text-[10px] uppercase tracking-widest font-bold text-slate-400">
        {{ $label }}
    </h4>
    <p @class([
        'font-semibold text-slate-700',
        'italic text-slate-400' => $value === 'Unknown' || $value === 'N/A' || !$value
    ])>
        {{ $value ?: 'Unknown' }}
    </p>
</div>