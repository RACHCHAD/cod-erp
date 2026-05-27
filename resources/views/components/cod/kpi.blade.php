@props([
    'label',
    'value',
    'icon' => 'heroicon-o-square-3-stack-3d',
    'color' => 'primary',
])

@php
    $tones = [
        'primary' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300',
        'success' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
        'danger' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
        'info' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
        'gray' => 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300',
    ];
    $tone = $tones[$color] ?? $tones['primary'];
@endphp

<div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-5 shadow-sm transition hover:shadow">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $value }}</p>
        </div>
        <div class="rounded-lg p-2 {{ $tone }}">
            <x-filament::icon :icon="$icon" class="w-5 h-5" />
        </div>
    </div>
</div>
