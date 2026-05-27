<x-filament-panels::page>
    @php($m = $this->getMetrics())

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <x-cod.kpi label="CPL" :value="'$'.number_format($m['cpl'], 2)" icon="heroicon-o-calculator" />
        <x-cod.kpi label="CPA" :value="'$'.number_format($m['cpa'], 2)" icon="heroicon-o-user-plus" color="info" />
        <x-cod.kpi label="ROAS" :value="number_format($m['roas'], 2).'x'" icon="heroicon-o-rocket-launch" :color="$m['roas'] >= 2 ? 'success' : 'warning'" />
        <x-cod.kpi label="Margin" :value="number_format($m['margin'], 1).'%'" icon="heroicon-o-chart-pie" :color="$m['margin'] >= 30 ? 'success' : 'warning'" />
        <x-cod.kpi label="Confirmation Rate" :value="number_format($m['confirmation_rate'], 1).'%'" icon="heroicon-o-check-circle" color="info" />
        <x-cod.kpi label="Delivery Rate" :value="number_format($m['delivery_rate'], 1).'%'" icon="heroicon-o-truck" color="success" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <x-filament::section heading="Platform breakdown (30d)">
            <x-cod.table :headings="['Platform', 'Spend', 'Leads', 'CPL']">
                @foreach($this->getPlatformBreakdown() as $row)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <td class="px-3 py-2">
                            <span class="inline-block w-2 h-2 rounded-full mr-2" style="background-color: {{ $row->color }}"></span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $row->name }}</span>
                        </td>
                        <td class="px-3 py-2">${{ number_format($row->spend, 2) }}</td>
                        <td class="px-3 py-2">{{ number_format($row->leads) }}</td>
                        <td class="px-3 py-2">${{ number_format($row->cpl, 2) }}</td>
                    </tr>
                @endforeach
            </x-cod.table>
        </x-filament::section>

        <x-filament::section heading="Team performance leaderboard (30d)">
            <x-cod.table :headings="['Agent', 'Orders', 'Delivered', 'Revenue', 'Profit']">
                @foreach($this->getTeamPerformance() as $row)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $row->name }}</td>
                        <td class="px-3 py-2">{{ $row->total_orders }}</td>
                        <td class="px-3 py-2 text-emerald-600">{{ $row->delivered_orders }}</td>
                        <td class="px-3 py-2">${{ number_format($row->revenue ?? 0, 2) }}</td>
                        <td class="px-3 py-2 font-semibold {{ ($row->profit ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            ${{ number_format($row->profit ?? 0, 2) }}
                        </td>
                    </tr>
                @endforeach
            </x-cod.table>
        </x-filament::section>
    </div>
</x-filament-panels::page>
