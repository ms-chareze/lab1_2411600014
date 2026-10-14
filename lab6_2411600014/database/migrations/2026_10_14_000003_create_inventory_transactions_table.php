<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Part 1 - Inventory transaction ledger.
 *
 * Answers to the "Important Considerations":
 *  - Quantity IS signed: positive = stock added, negative = stock removed.
 *    One column makes SUM(quantity) the net movement and keeps adjustments
 *    (which can go either way) simple.
 *  - Deleting a product: product_id uses RESTRICT, and products are soft
 *    deleted, so history is never lost. A ledger must not silently vanish.
 *  - Deleting a user: user_id is set to NULL so the record survives.
 *  - Indexes: (product_id, created_at) serves the product history page and
 *    date filters; `type` and `created_at` serve the index/report filters.
 *  - quantity_before / quantity_after snapshot the stock level around each
 *    movement, giving the "running balance" for free.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20); // stock_in | stock_out | adjustment
            $table->integer('quantity'); // signed
            $table->unsignedInteger('quantity_before')->default(0);
            $table->unsignedInteger('quantity_after')->default(0);
            $table->string('reference_number', 100)->nullable();
            $table->string('reason', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
