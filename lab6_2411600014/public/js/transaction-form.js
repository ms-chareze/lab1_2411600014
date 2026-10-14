/**
 * Record-transaction form (Lab 6, Part 5 Step 3 "JavaScript Enhancement").
 *  - shows the selected product's current stock
 *  - for Stock Out, caps the quantity input at the stock on hand
 * Stock data comes from window.PRODUCT_STOCK ({ id: {quantity, reorder} }),
 * printed by the Blade view. The server re-validates everything regardless.
 */
(function () {
    'use strict';

    var form = document.getElementById('transaction-form');
    if (!form) { return; }

    var stock    = window.PRODUCT_STOCK || {};
    var product  = document.getElementById('product_id');
    var quantity = document.getElementById('quantity');
    var info     = document.getElementById('stock-info');
    var hint     = document.getElementById('quantity-hint');

    // Searchable dropdown (Tom Select). The page still works if the CDN is blocked.
    if (window.TomSelect) {
        new TomSelect(product, { create: false, maxOptions: 500, allowEmptyOption: true });
    }

    function currentType() {
        var checked = form.querySelector('input[name="type"]:checked');
        return checked ? checked.value : null;
    }

    function update() {
        var p = stock[product.value];

        if (!p) {
            info.textContent = 'Select a product to see its current stock.';
            quantity.removeAttribute('max');
            hint.textContent = '';
            return;
        }

        info.textContent = 'Current stock: ' + p.quantity + ' (reorder level ' + p.reorder + ')';

        if (currentType() === 'stock_out') {
            quantity.max = p.quantity;
            hint.textContent = p.quantity > 0
                ? 'Maximum you can remove: ' + p.quantity
                : 'This product has no stock to remove.';
        } else {
            quantity.removeAttribute('max');
            hint.textContent = '';
        }
    }

    product.addEventListener('change', update);
    form.querySelectorAll('input[name="type"]').forEach(function (radio) {
        radio.addEventListener('change', update);
    });

    update();
})();
