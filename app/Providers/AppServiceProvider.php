<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::unguard();

        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);

        Gate::before(function ($user) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
