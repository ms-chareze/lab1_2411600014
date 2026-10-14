<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Products table (the Lab 5 prerequisite, rebuilt here so Lab 6 is self-contained).
 *
 * Design notes:
 *  - `quantity` is the live stock level. It is only ever changed through
 *    InventoryTransaction records (see InventoryTransactionObserver).
 *  - `low_stock_notified_at` remembers when a low-stock alert was sent so we do
 *    not spam managers with duplicates while the product stays low.
 *  - Soft deletes keep the audit trail intact: transactions of a deleted
 *    product still resolve to a product name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name');
            $table->string('category', 100)->index();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reorder_level')->default(10);
            $table->timestamp('low_stock_notified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
