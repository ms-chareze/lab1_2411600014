<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * The InventoryTransaction observer is registered with the #[ObservedBy]
     * attribute on the model, and the LowStockDetected listener is
     * auto-discovered, so only authorization gates are needed here.
     */
    public function boot(): void
    {
        // Admin-only: delete products, delete (reverse) transactions.
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // Admin + manager: create/edit products, stock adjustments.
        Gate::define('manage-stock', fn (User $user) => $user->canManageStock());
    }
}
