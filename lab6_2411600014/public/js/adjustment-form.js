/**
 * Stock adjustment form (Lab 6, Part 8 Step 2).
 * The read-only "System Quantity" is loaded via AJAX from
 * GET /api/products/{id}/stock, and the difference is previewed live.
 * (The server recalculates the difference itself when the form is submitted.)
 */
(function () {
    'use strict';

    var form = document.getElementById('adjustment-form');
    if (!form) { return; }

    var baseUrl  = form.dataset.stockUrl;
    var product  = document.getElementById('product_id');
    var system   = document.getElementById('system_quantity');
    var status   = document.getElementById('system-status');
    var physical = document.getElementById('physical_count');
    var diff     = document.getElementById('difference');
    var systemQty = null;
    var requestId = 0;

    if (window.TomSelect) {
        new TomSelect(product, { create: false, maxOptions: 500, allowEmptyOption: true });
    }

    function showDifference() {
        var entered = physical.value === '' ? NaN : parseInt(physical.value, 10);

        if (systemQty === null || isNaN(entered)) {
            diff.value = '-';
            diff.className = 'form-control';
            return;
        }

        var d = entered - systemQty;
        diff.value = (d > 0 ? '+' : '') + d;
        diff.className = 'form-control ' + (d > 0 ? 'text-success' : (d < 0 ? 'text-danger' : ''));
    }

    function loadStock() {
        var id = product.value;
        systemQty = null;
        system.value = '-';
        showDifference();

        if (!id) {
            status.textContent = 'Loaded when you pick a product.';
            return;
        }

        status.textContent = 'Loading...';
        var current = ++requestId; // ignore out-of-order responses

        fetch(baseUrl + '/' + encodeURIComponent(id) + '/stock', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) {
                if (!response.ok) { throw new Error('HTTP ' + response.status); }
                return response.json();
            })
            .then(function (payload) {
                if (current !== requestId) { return; }
                systemQty = payload.data.quantity;
                system.value = systemQty;
                status.textContent = payload.data.stock_status + ' (reorder level ' + payload.data.reorder_level + ')';
                showDifference();
            })
            .catch(function () {
                if (current !== requestId) { return; }
                status.textContent = 'Could not load the current quantity. Please try again.';
            });
    }

    product.addEventListener('change', loadStock);
    physical.addEventListener('input', showDifference);

    if (product.value) { loadStock(); }
})();
