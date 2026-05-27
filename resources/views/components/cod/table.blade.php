@props(['headings' => []])

<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
        <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
            <tr>
                @foreach ($headings as $h)
                    <th class="px-3 py-2 font-medium">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
