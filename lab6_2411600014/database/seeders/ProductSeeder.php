<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

/**
 * Hardware-store products. Stock is loaded through the InventoryService, so the
 * ledger, running balances and low stock alerts are all populated exactly as
 * they would be in real use (products start at 0 and receive opening stock).
 */
class ProductSeeder extends Seeder
{
    public function run(InventoryService $inventory): void
    {
        if (Product::exists()) {
            return; // idempotent: do not double-load stock on a second `db:seed`
        }

        $admin   = User::where('email', 'admin@gmail.com')->first();
        $manager = User::where('email', 'manager@gmail.com')->first();
        $staff   = User::where('email', 'staff@gmail.com')->first();

        // sku, name, category, price, opening stock, reorder level
        $catalog = [
            ['CEM-001', 'Portland Cement 40kg',        'Cement & Concrete', 265.00, 120, 30],
            ['CEM-002', 'Ready-Mix Mortar 25kg',       'Cement & Concrete', 190.00,  45, 20],
            ['STL-001', 'Deformed Bar 10mm x 6m',      'Steel',             185.00, 200, 50],
            ['STL-002', 'GI Wire #16 (1kg)',           'Steel',             115.00,  60, 25],
            ['LUM-001', 'Coco Lumber 2x2x10',          'Lumber',            145.00,  80, 25],
            ['LUM-002', 'Marine Plywood 1/2" 4x8',     'Lumber',            920.00,  18, 10],
            ['PLB-001', 'PVC Pipe 1/2" x 3m',          'Plumbing',           78.00,  90, 30],
            ['PLB-002', 'Gate Valve 3/4"',             'Plumbing',          210.00,  14, 10],
            ['ELC-001', 'THHN Wire #12 (75m roll)',    'Electrical',      2450.00,  12,  5],
            ['ELC-002', 'LED Bulb 9W',                 'Electrical',        85.00, 150, 40],
            ['PNT-001', 'Latex Paint White 4L',        'Paint',            720.00,  30, 12],
            ['PNT-002', 'Paint Roller Set',            'Paint',            165.00,   9, 10],
            ['TLS-001', 'Claw Hammer 16oz',            'Tools',            320.00,  25,  8],
            ['TLS-002', 'Cordless Drill 12V',          'Tools',           2890.00,   6,  4],
            ['FST-001', 'Common Nails 3" (1kg)',       'Fasteners',         95.00, 100, 30],
            ['FST-002', 'Tek Screws 1" (100pcs)',      'Fasteners',         65.00,  70, 25],
        ];

        $products = [];
        foreach ($catalog as [$sku, $name, $category, $price, $opening, $reorder]) {
            $product = Product::create([
                'sku'           => $sku,
                'name'          => $name,
                'category'      => $category,
                'description'   => "{$name} - standard stock item.",
                'price'         => $price,
                'quantity'      => 0,
                'reorder_level' => $reorder,
            ]);

            $inventory->record($product, InventoryTransaction::TYPE_IN, $opening, $admin, [
                'reference_number' => 'OPENING',
                'notes'            => 'Opening balance.',
            ]);

            $products[$sku] = $product->fresh();
        }

        // A few realistic movements, by different users (accountability trail).
        $inventory->record($products['CEM-001'], InventoryTransaction::TYPE_OUT, 35, $staff,   ['reference_number' => 'SO-1001', 'notes' => 'Sold to contractor.']);
        $inventory->record($products['STL-001'], InventoryTransaction::TYPE_OUT, 40, $staff,   ['reference_number' => 'SO-1002']);
        $inventory->record($products['LUM-002'], InventoryTransaction::TYPE_OUT,  6, $staff,   ['reference_number' => 'SO-1003']);
        $inventory->record($products['PLB-002'], InventoryTransaction::TYPE_OUT,  5, $manager, ['reference_number' => 'SO-1004']); // 14 -> 9: low stock alert
        $inventory->record($products['ELC-002'], InventoryTransaction::TYPE_IN,  60, $manager, ['reference_number' => 'PO-2001', 'notes' => 'Supplier delivery.']);
        $inventory->adjust($products['TLS-002'], 5, 'Damaged goods', 'One drill damaged in storage.', $manager);
    }
}
