<x-filament-panels::page>
    <form wire:submit.prevent="$refresh">
        {{ $this->form }}
    </form>

    @php($m = $this->getMetrics())

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-cod.kpi label="Revenue" :value="'$'.number_format($m['revenue'], 2)" color="success" icon="heroicon-o-currency-dollar" />
        <x-cod.kpi label="Product cost" :value="'$'.number_format($m['product_cost'], 2)" color="gray" icon="heroicon-o-cube" />
        <x-cod.kpi label="Delivery cost" :value="'$'.number_format($m['delivery_cost'], 2)" color="gray" icon="heroicon-o-truck" />
        <x-cod.kpi label="Ad spend" :value="'$'.number_format($m['ad_spend'], 2)" color="warning" icon="heroicon-o-megaphone" />
        <x-cod.kpi label="Refunds" :value="'$'.number_format($m['refunds'], 2)" color="danger" icon="heroicon-o-arrow-uturn-left" />
        <x-cod.kpi label="Other expenses" :value="'$'.number_format($m['misc_expenses'], 2)" color="gray" icon="heroicon-o-ellipsis-horizontal-circle" />
        <x-cod.kpi label="Net profit" :value="'$'.number_format($m['net_profit'], 2)" :color="$m['net_profit'] >= 0 ? 'success' : 'danger'" icon="heroicon-o-banknotes" />
        <x-cod.kpi label="Margin" :value="number_format($m['margin'], 1).'%'" :color="$m['margin'] >= 30 ? 'success' : ($m['margin'] >= 15 ? 'warning' : 'danger')" icon="heroicon-o-chart-pie" />
        <x-cod.kpi label="ROAS" :value="number_format($m['roas'], 2).'x'" :color="$m['roas'] >= 2 ? 'success' : ($m['roas'] >= 1 ? 'warning' : 'danger')" icon="heroicon-o-rocket-launch" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <x-filament::section heading="Profit by product">
            <x-cod.table :headings="['Product', 'Units', 'Revenue', 'Cost', 'Profit']">
                @foreach($this->getProductBreakdown() as $row)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $row->name }}</td>
                        <td class="px-3 py-2">{{ number_format($row->units) }}</td>
                        <td class="px-3 py-2">${{ number_format($row->revenue, 2) }}</td>
                        <td class="px-3 py-2 text-gray-500">${{ number_format($row->cost, 2) }}</td>
                        <td class="px-3 py-2 font-semibold {{ $row->profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            ${{ number_format($row->profit, 2) }}
                        </td>
                    </tr>
                @endforeach
            </x-cod.table>
        </x-filament::section>

        <x-filament::section heading="Profit by country">
            <x-cod.table :headings="['Country', 'Orders', 'Revenue', 'Profit']">
                @foreach($this->getCountryBreakdown() as $row)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $row->flag }} {{ $row->name }}</td>
                        <td class="px-3 py-2">{{ $row->orders }}</td>
                        <td class="px-3 py-2">${{ number_format($row->revenue, 2) }}</td>
                        <td class="px-3 py-2 font-semibold {{ $row->profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            ${{ number_format($row->profit, 2) }}
                        </td>
                    </tr>
                @endforeach
            </x-cod.table>
        </x-filament::section>
    </div>
</x-filament-panels::page>
