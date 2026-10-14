<?php

namespace Tests\Feature;

use App\Events\LowStockDetected;
use App\Listeners\SendLowStockNotification;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Automated version of the Part 10 testing checklist.
 * Run with:  php artisan test --filter=InventoryTrackingTest
 */
class InventoryTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function product(int $quantity = 50, int $reorder = 10): Product
    {
        static $n = 0;
        $n++;

        return Product::create([
            'sku' => "TEST-{$n}", 'name' => "Test Product {$n}", 'category' => 'Test',
            'price' => 100, 'quantity' => $quantity, 'reorder_level' => $reorder,
        ]);
    }

    public function test_stock_in_increases_quantity(): void   // checklist 1
    {
        $admin = $this->userWithRole('admin');
        $product = $this->product(50);

        $this->actingAs($admin)->post(route('transactions.store'), [
            'product_id' => $product->id, 'type' => 'stock_in', 'quantity' => 25, 'reference_number' => 'PO-1',
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(75, $product->fresh()->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id, 'user_id' => $admin->id, 'type' => 'stock_in',
            'quantity' => 25, 'quantity_before' => 50, 'quantity_after' => 75,
        ]);
    }

    public function test_stock_out_decreases_quantity(): void   // checklist 2
    {
        $product = $this->product(50);

        $this->actingAs($this->userWithRole('staff'))->post(route('transactions.store'), [
            'product_id' => $product->id, 'type' => 'stock_out', 'quantity' => 20,
        ])->assertRedirect();

        $this->assertSame(30, $product->fresh()->quantity);
        $this->assertDatabaseHas('inventory_transactions', ['product_id' => $product->id, 'quantity' => -20]);
    }

    public function test_stock_out_exceeding_stock_is_rejected(): void   // checklist 3
    {
        $product = $this->product(5);

        $this->actingAs($this->userWithRole('staff'))
            ->from(route('transactions.create'))
            ->post(route('transactions.store'), ['product_id' => $product->id, 'type' => 'stock_out', 'quantity' => 6])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(5, $product->fresh()->quantity);
        $this->assertDatabaseCount('inventory_transactions', 0);
    }

    public function test_observer_blocks_negative_stock_even_without_the_form_request(): void
    {
        $product = $this->product(5);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        try {
            app(\App\Services\InventoryService::class)->record($product, 'stock_out', 6);
        } finally {
            $this->assertSame(5, $product->fresh()->quantity);
            $this->assertDatabaseCount('inventory_transactions', 0); // rolled back
        }
    }

    public function test_low_stock_event_fires_once_and_rearms_after_restock(): void   // checklist 4
    {
        Event::fake([LowStockDetected::class]);
        $service = app(\App\Services\InventoryService::class);
        $product = $this->product(15, 10);

        $service->record($product, 'stock_out', 6);   // 9  -> low, alert
        $service->record($product, 'stock_out', 2);   // 7  -> still low, NO duplicate
        Event::assertDispatchedTimes(LowStockDetected::class, 1);

        $service->record($product, 'stock_in', 20);   // 27 -> restocked, flag cleared
        $this->assertNull($product->fresh()->low_stock_notified_at);

        $service->record($product, 'stock_out', 20);  // 7  -> low again, alert again
        Event::assertDispatchedTimes(LowStockDetected::class, 2);
    }

    public function test_listener_notifies_admins_and_managers_only(): void
    {
        Notification::fake();
        $admin   = $this->userWithRole('admin');
        $manager = $this->userWithRole('manager');
        $staff   = $this->userWithRole('staff');
        $product = $this->product(3, 10);

        (new SendLowStockNotification)->handle(new LowStockDetected($product));

        Notification::assertSentTo([$admin, $manager], LowStockNotification::class);
        Notification::assertNotSentTo($staff, LowStockNotification::class);
    }

    public function test_notification_is_stored_for_the_bell(): void
    {
        $admin   = $this->userWithRole('admin');
        $product = $this->product(3, 10);

        $admin->notify(new LowStockNotification($product));

        $this->assertSame(1, $admin->unreadNotifications()->count());
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee($product->name);

        $id = $admin->notifications()->first()->id;
        $this->actingAs($admin)->post(route('notifications.read', $id))
            ->assertRedirect(route('products.show', $product));
        $this->assertSame(0, $admin->unreadNotifications()->count());
    }

    public function test_dashboard_api_returns_accurate_stats(): void   // checklist 5
    {
        $a = $this->product(10, 5);   // value 1000
        $b = $this->product(2, 5);    // low, value 200
        $c = $this->product(0, 5);    // out of stock
        $admin = $this->userWithRole('admin');

        $stats = $this->actingAs($admin)->getJson(route('api.dashboard.stats'))
            ->assertOk()
            ->assertJsonPath('data.total_products', 3)
            ->assertJsonPath('data.total_units', 12)
            ->assertJsonPath('data.low_stock_count', 2)
            ->assertJsonPath('data.out_of_stock_count', 1)
            ->assertJsonStructure(['meta' => ['generated_at']]);

        // Compared loosely: the value may be encoded as 1200 or 1200.0.
        $this->assertEquals(1200, $stats->json('data.inventory_value'));

        $this->getJson(route('api.products.low-stock'))
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.count', 2);

        app(\App\Services\InventoryService::class)->record($a, 'stock_in', 1, $admin);
        $this->getJson(route('api.transactions.recent'))
            ->assertOk()->assertJsonPath('data.0.type', 'stock_in')->assertJsonPath('data.0.signed_quantity', '+1');
    }

    public function test_api_requires_authentication(): void
    {
        $this->getJson(route('api.dashboard.stats'))->assertUnauthorized();
    }

    public function test_adjustment_sets_stock_to_physical_count(): void   // checklist 7
    {
        $manager = $this->userWithRole('manager');
        $product = $this->product(20);

        $this->actingAs($manager)->post(route('adjustments.store'), [
            'product_id' => $product->id, 'physical_count' => 17, 'reason' => 'Damaged goods',
        ])->assertRedirect();

        $this->assertSame(17, $product->fresh()->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id, 'type' => 'adjustment', 'quantity' => -3,
            'reason' => 'Damaged goods', 'user_id' => $manager->id,
        ]);

        // Upward adjustment
        $this->post(route('adjustments.store'), [
            'product_id' => $product->id, 'physical_count' => 25, 'reason' => 'Physical count discrepancy',
        ]);
        $this->assertSame(25, $product->fresh()->quantity);

        // No difference -> rejected
        $this->post(route('adjustments.store'), [
            'product_id' => $product->id, 'physical_count' => 25, 'reason' => 'Other',
        ])->assertSessionHasErrors('physical_count');
    }

    public function test_product_history_shows_running_balance(): void   // checklist 8
    {
        $admin = $this->userWithRole('admin');
        $product = $this->product(10);
        app(\App\Services\InventoryService::class)->record($product, 'stock_in', 5, $admin);

        $this->actingAs($admin)->get(route('products.show', $product))
            ->assertOk()->assertSee('Balance After')->assertSee('+5')->assertSee('15');
    }

    public function test_validation_rules_reject_invalid_input(): void   // checklist 9
    {
        $this->actingAs($this->userWithRole('admin'));
        $product = $this->product();

        $this->post(route('transactions.store'), [])
            ->assertSessionHasErrors(['product_id', 'type', 'quantity']);
        $this->post(route('transactions.store'), ['product_id' => 9999, 'type' => 'stock_in', 'quantity' => 1])
            ->assertSessionHasErrors('product_id');
        $this->post(route('transactions.store'), ['product_id' => $product->id, 'type' => 'adjustment', 'quantity' => 1])
            ->assertSessionHasErrors('type');
        $this->post(route('transactions.store'), ['product_id' => $product->id, 'type' => 'stock_in', 'quantity' => 0])
            ->assertSessionHasErrors('quantity');
        $this->post(route('transactions.store'), ['product_id' => $product->id, 'type' => 'stock_in', 'quantity' => 'abc'])
            ->assertSessionHasErrors('quantity');
    }

    public function test_unauthorized_users_cannot_use_admin_features(): void   // checklist 10
    {
        $staff = $this->userWithRole('staff');
        $manager = $this->userWithRole('manager');
        $product = $this->product();
        $tx = app(\App\Services\InventoryService::class)->record($product, 'stock_in', 1, $staff);

        $this->actingAs($staff);
        $this->get(route('products.create'))->assertForbidden();
        $this->get(route('adjustments.create'))->assertForbidden();
        $this->delete(route('products.destroy', $product))->assertForbidden();
        $this->delete(route('transactions.destroy', $tx))->assertForbidden();

        $this->actingAs($manager);
        $this->get(route('adjustments.create'))->assertOk();
        $this->delete(route('transactions.destroy', $tx))->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
        $this->get(route('transactions.index'))->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_delete_reverses_the_stock_effect(): void
    {
        $admin = $this->userWithRole('admin');
        $product = $this->product(10);
        $tx = app(\App\Services\InventoryService::class)->record($product, 'stock_in', 5, $admin);
        $this->assertSame(15, $product->fresh()->quantity);

        $this->actingAs($admin)->delete(route('transactions.destroy', $tx))->assertRedirect(route('transactions.index'));

        $this->assertSame(10, $product->fresh()->quantity);
        $this->assertDatabaseCount('inventory_transactions', 0);
    }

    public function test_delete_is_refused_when_reversal_would_go_negative(): void
    {
        $admin = $this->userWithRole('admin');
        $product = $this->product(0);
        $service = app(\App\Services\InventoryService::class);
        $in = $service->record($product, 'stock_in', 5, $admin);
        $service->record($product, 'stock_out', 5, $admin);   // stock is 0 again

        $this->actingAs($admin)->from(route('transactions.index'))
            ->delete(route('transactions.destroy', $in))
            ->assertSessionHas('error');

        $this->assertSame(0, $product->fresh()->quantity);
        $this->assertDatabaseCount('inventory_transactions', 2);
    }

    public function test_product_creation_records_opening_stock_and_soft_delete_keeps_history(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('products.store'), [
            'sku' => 'NEW-1', 'name' => 'New Item', 'category' => 'Test', 'price' => 12.5,
            'initial_quantity' => 40, 'reorder_level' => 10,
        ])->assertRedirect();

        $product = Product::where('sku', 'NEW-1')->firstOrFail();
        $this->assertSame(40, $product->quantity);
        $this->assertSame(1, $product->transactions()->count());

        $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));
        $this->assertSoftDeleted($product);
        $this->assertSame(1, InventoryTransaction::where('product_id', $product->id)->count());
        $this->get(route('transactions.index'))->assertOk()->assertSee('New Item');
    }

    public function test_reports_render_and_export_csv(): void
    {
        $admin = $this->userWithRole('admin');
        $product = $this->product(3, 10);
        app(\App\Services\InventoryService::class)->record($product, 'stock_in', 1, $admin);

        foreach (['movement', 'low-stock', 'valuation'] as $report) {
            $this->actingAs($admin)->get(route('reports.index', ['report' => $report]))->assertOk();
        }

        $response = $this->get(route('reports.export', ['report' => 'low-stock']));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString($product->sku, $response->streamedContent());
    }

    public function test_helper_methods_and_scopes(): void
    {
        $ok  = $this->product(50, 10);
        $low = $this->product(10, 10);
        $out = $this->product(0, 10);

        $this->assertSame('In Stock', $ok->stock_status);
        $this->assertSame('success', $ok->stock_status_color);
        $this->assertTrue($low->isLowStock());
        $this->assertSame('Low Stock', $low->stock_status);
        $this->assertSame('warning', $low->stock_status_color);
        $this->assertTrue($out->isOutOfStock());
        $this->assertSame('danger', $out->stock_status_color);

        $this->assertEqualsCanonicalizing([$low->id, $out->id], Product::lowStock()->pluck('id')->all());
        $this->assertSame([$out->id], Product::outOfStock()->pluck('id')->all());
    }
}
