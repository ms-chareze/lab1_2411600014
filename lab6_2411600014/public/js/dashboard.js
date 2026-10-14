/**
 * Inventory dashboard - AJAX polling (Lab 6, Part 7 Step 3).
 *
 * Organised with the module pattern: everything lives inside one IIFE and
 * only reads its configuration from data-* attributes on #inventory-dashboard,
 * so there are no globals and no hard-coded URLs.
 *
 * Every `interval` ms it:
 *   1. fetches the three API endpoints
 *   2. updates the DOM with the new values
 *   3. animates number changes (and flashes the card)
 *   4. updates the "Last updated" timestamp
 *   5. handles errors gracefully (keeps old data, shows a status, retries)
 */
(function () {
    'use strict';

    var root = document.getElementById('inventory-dashboard');
    if (!root) { return; }

    var config = {
        statsUrl:    root.dataset.statsUrl,
        lowStockUrl: root.dataset.lowStockUrl,
        recentUrl:   root.dataset.recentUrl,
        interval:    parseInt(root.dataset.interval, 10) || 30000
    };

    var el = {
        lastUpdated: document.getElementById('last-updated'),
        status:      document.getElementById('live-status'),
        dot:         document.getElementById('live-dot'),
        lowBody:     document.getElementById('low-stock-body'),
        lowCount:    document.getElementById('low-stock-badge'),
        recentBody:  document.getElementById('recent-tx-body'),
        refreshBtn:  document.getElementById('refresh-now')
    };

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var timer = null;
    var busy = false;

    /* ---------- helpers ---------- */

    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    var formatters = {
        int: function (n) { return Math.round(n).toLocaleString('en-US'); },
        currency: function (n) {
            return '₱' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };

    function getJson(url) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) {
            if (response.status === 401 || response.status === 419) {
                var err = new Error('Your session has expired. Please log in again.');
                err.fatal = true;
                throw err;
            }
            if (!response.ok) { throw new Error('Server responded with ' + response.status); }
            return response.json();
        });
    }

    /** Count a number up/down to its new value (instantly if the user prefers reduced motion). */
    function animateNumber(node, target) {
        var format = formatters[node.dataset.format || 'int'];
        var from = parseFloat(node.dataset.value);
        if (isNaN(from)) { from = 0; }
        if (from === target) { return; }

        node.dataset.value = target;
        node.classList.remove('stat-updated');
        void node.offsetWidth; // restart the CSS animation
        node.classList.add('stat-updated');

        if (reduceMotion) {
            node.textContent = format(target);
            return;
        }

        var duration = 700;
        var start = null;

        function step(now) {
            if (start === null) { start = now; }
            var t = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - t, 3);
            node.textContent = format(from + (target - from) * eased);
            if (t < 1) { window.requestAnimationFrame(step); } else { node.textContent = format(target); }
        }
        window.requestAnimationFrame(step);
    }

    /* ---------- rendering ---------- */

    function renderStats(stats) {
        document.querySelectorAll('[data-stat]').forEach(function (node) {
            var key = node.dataset.stat;
            if (Object.prototype.hasOwnProperty.call(stats, key)) {
                animateNumber(node, Number(stats[key]));
            }
        });
    }

    function renderLowStock(payload) {
        var rows = payload.data || [];
        if (el.lowCount) { el.lowCount.textContent = (payload.meta && payload.meta.count) || rows.length; }

        if (!rows.length) {
            el.lowBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">All products are sufficiently stocked. 🎉</td></tr>';
            return;
        }

        el.lowBody.innerHTML = rows.map(function (p) {
            return '<tr>' +
                '<td><a href="' + esc(p.url) + '" class="fw-semibold text-decoration-none">' + esc(p.name) + '</a>' +
                    '<div class="small text-muted">' + esc(p.sku) + '</div></td>' +
                '<td class="text-center fw-bold">' + esc(p.quantity) + '</td>' +
                '<td class="text-center">' + esc(p.reorder_level) + '</td>' +
                '<td><span class="badge bg-' + esc(p.stock_status_color) + '">' + esc(p.stock_status) + '</span></td>' +
                '</tr>';
        }).join('');
    }

    function renderRecent(payload) {
        var rows = payload.data || [];

        if (!rows.length) {
            el.recentBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No transactions yet.</td></tr>';
            return;
        }

        el.recentBody.innerHTML = rows.map(function (t) {
            var name = t.product.url
                ? '<a href="' + esc(t.product.url) + '" class="text-decoration-none">' + esc(t.product.name) + '</a>'
                : esc(t.product.name);
            var qtyClass = t.quantity > 0 ? 'qty-plus' : 'qty-minus';

            return '<tr>' +
                '<td class="small text-muted">' + esc(t.created_at_human) + '</td>' +
                '<td>' + name + '</td>' +
                '<td><span class="badge bg-' + esc(t.type_color) + '">' + esc(t.type_label) + '</span></td>' +
                '<td class="text-end ' + qtyClass + '">' + esc(t.signed_quantity) + '</td>' +
                '<td class="small">' + esc(t.user) + '</td>' +
                '</tr>';
        }).join('');
    }

    function setStatus(ok, message) {
        if (el.dot) { el.dot.classList.toggle('error', !ok); }
        if (el.status) { el.status.textContent = message; }
    }

    function stamp() {
        if (el.lastUpdated) { el.lastUpdated.textContent = new Date().toLocaleTimeString(); }
    }

    /* ---------- polling ---------- */

    function refresh() {
        if (busy) { return Promise.resolve(); }
        busy = true;
        if (el.refreshBtn) { el.refreshBtn.disabled = true; }

        return Promise.all([getJson(config.statsUrl), getJson(config.lowStockUrl), getJson(config.recentUrl)])
            .then(function (results) {
                renderStats(results[0].data);
                renderLowStock(results[1]);
                renderRecent(results[2]);
                stamp();
                setStatus(true, 'Live - refreshes every ' + Math.round(config.interval / 1000) + 's');
            })
            .catch(function (error) {
                // Keep showing the last good data; just tell the user and try again next tick.
                setStatus(false, error.fatal ? error.message : 'Update failed - retrying automatically');
                if (error.fatal) { stop(); }
            })
            .then(function () {
                busy = false;
                if (el.refreshBtn) { el.refreshBtn.disabled = false; }
            });
    }

    function start() {
        stop();
        timer = window.setInterval(function () {
            if (!document.hidden) { refresh(); } // do not hit the server from background tabs
        }, config.interval);
    }

    function stop() {
        if (timer) { window.clearInterval(timer); timer = null; }
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && timer) { refresh(); }
    });

    if (el.refreshBtn) {
        el.refreshBtn.addEventListener('click', function () { refresh(); });
    }

    start();
})();
