<?php

namespace App\Observers;

use App\Models\OrderItem;

class OrderItemObserver
{
    public function saved(OrderItem $item): void
    {
        $order = $item->order;
        if (! $order) {
            return;
        }
        $order->recalculate();
        $order->saveQuietly();
    }

    public function deleted(OrderItem $item): void
    {
        $order = $item->order;
        if (! $order) {
            return;
        }
        $order->recalculate();
        $order->saveQuietly();
    }
}
