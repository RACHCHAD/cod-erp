<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Stock;
use App\Models\StockMovement;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $newStatus = $order->status;
        $oldStatus = $order->getOriginal('status');

        if ($oldStatus !== Order::STATUS_CONFIRMED && $newStatus === Order::STATUS_CONFIRMED) {
            $this->decrementStock($order);
        }

        if ($oldStatus === Order::STATUS_CONFIRMED && in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_RETURNED, Order::STATUS_REFUNDED], true)) {
            $this->restockOrder($order);
        }
    }

    protected function decrementStock(Order $order): void
    {
        $order->loadMissing('items', 'warehouse');
        if (! $order->warehouse_id) {
            return;
        }
        foreach ($order->items as $item) {
            $stock = Stock::firstWhere([
                'product_id' => $item->product_id,
                'warehouse_id' => $order->warehouse_id,
            ]);
            if (! $stock) {
                continue;
            }
            $stock->quantity = max(0, $stock->quantity - $item->quantity);
            $stock->save();

            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => 'out',
                'quantity' => $item->quantity,
                'order_id' => $order->id,
                'notes' => 'Auto deducted on order confirmation',
            ]);
        }
    }

    protected function restockOrder(Order $order): void
    {
        $order->loadMissing('items');
        if (! $order->warehouse_id) {
            return;
        }
        foreach ($order->items as $item) {
            $type = $order->status === Order::STATUS_RETURNED ? 'returned' : 'in';
            $stock = Stock::firstWhere([
                'product_id' => $item->product_id,
                'warehouse_id' => $order->warehouse_id,
            ]);
            if (! $stock) {
                continue;
            }
            if ($type === 'returned') {
                $stock->returned_quantity += $item->quantity;
            }
            $stock->quantity += $item->quantity;
            $stock->save();

            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => $type,
                'quantity' => $item->quantity,
                'order_id' => $order->id,
                'notes' => "Auto restock from $order->status",
            ]);
        }
    }
}
