/**
 * Lab 6 Part 7 - AJAX polling for the dashboard.
 *
 *  - fetches /api/dashboard/stats, /api/students/low-load and /api/transactions/recent
 *    every 30 seconds (and when you press "Refresh now")
 *  - updates the DOM, animates number changes, updates "Last Updated"
 *  - pauses while the browser tab is hidden
 *  - handles errors gracefully (status badge + keeps the old data on screen)
 *
 * Configuration is provided by the Blade view as window.DashboardConfig.
 */
(function () {
    'use strict';

    const cfg = window.DashboardConfig || {};
    const INTERVAL = cfg.intervalMs || 30000;

    let timerId = null;
    let inFlight = false;

    const byId = (id) => document.getElementById(id);

    const STAT_TARGETS = [
        { key: 'total_students',     id: 'stat-total-students',     decimals: 0 },
        { key: 'average_gpa',        id: 'stat-average-gpa',        decimals: 2 },
        { key: 'warning_count',      id: 'stat-warning-count',      decimals: 0 },
        { key: 'total_units',        id: 'stat-total-units',        decimals: 0 },
        { key: 'low_load_count',     id: 'stat-low-load',           decimals: 0 },
        { key: 'transactions_today', id: 'stat-transactions-today', decimals: 0 },
    ];

    // ------------------------------------------------------------- helpers

    function formatNumber(value, decimals) {
        return Number(value).toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
    }

    function parseNumber(text) {
        return parseFloat(String(text).replace(/,/g, '')) || 0;
    }

    /** Small DOM builder: always uses textContent, so API data can never inject HTML. */
    function make(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }

    function link(href, text, className) {
        const a = make('a', className, text);
        a.href = href;
        return a;
    }

    // ----------------------------------------------------- number animation

    function animateNumber(el, target, decimals) {
        const from = el.dataset.value !== undefined ? parseFloat(el.dataset.value) : parseNumber(el.textContent);

        if (from === target) {
            el.dataset.value = String(target);
            return;
        }

        // visual feedback: brief highlight
        el.classList.remove('stat-updated');
        void el.offsetWidth; // restart the CSS animation
        el.classList.add('stat-updated');

        const duration = 700;
        const start = performance.now();

        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(from + (target - from) * eased, decimals);

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = formatNumber(target, decimals);
                el.dataset.value = String(target);
            }
        }

        requestAnimationFrame(step);
    }

    // ------------------------------------------------------------ renderers

    function renderStats(stats) {
        STAT_TARGETS.forEach(({ key, id, decimals }) => {
            const el = byId(id);
            if (el && stats[key] !== undefined) animateNumber(el, Number(stats[key]), decimals);
        });

        // notification bell badge
        const bell = byId('notificationCount');
        if (bell && stats.unread_notifications !== undefined) {
            bell.textContent = stats.unread_notifications;
            bell.classList.toggle('d-none', stats.unread_notifications <= 0);
        }
    }

    function renderLowLoad(students) {
        const list = byId('lowLoadList');
        const badge = byId('lowLoadBadge');
        if (!list) return;

        list.replaceChildren();
        if (badge) badge.textContent = students.length;

        if (students.length === 0) {
            const li = make('li', 'list-group-item text-center text-muted py-3', 'All students have a healthy unit load.');
            list.appendChild(li);
            return;
        }

        students.forEach((s) => {
            const li = make('li', 'list-group-item d-flex justify-content-between align-items-center');

            const left = make('span');
            left.appendChild(link(s.url, s.name, 'fw-bold text-decoration-none'));
            left.appendChild(make('span', 'text-muted small ms-1', `(${s.student_id})`));

            const right = make('span');
            right.appendChild(make('span', 'small text-muted me-2', `${s.enrolled_units} / min ${s.min_units} units`));
            const textDark = s.load_status_color === 'warning' ? ' text-dark' : '';
            right.appendChild(make('span', `badge bg-${s.load_status_color}${textDark}`, s.load_status));

            li.append(left, right);
            list.appendChild(li);
        });
    }

    function renderRecent(transactions) {
        const body = byId('recentTransactionsBody');
        if (!body) return;

        body.replaceChildren();

        if (transactions.length === 0) {
            const tr = make('tr');
            const td = make('td', 'text-center py-4 text-muted', 'No transactions recorded yet.');
            td.colSpan = 7;
            tr.appendChild(td);
            body.appendChild(tr);
            return;
        }

        transactions.forEach((t) => {
            const tr = make('tr');

            tr.appendChild(make('td', 'px-3 text-nowrap', t.created_at_human));

            const studentTd = make('td');
            studentTd.appendChild(t.student ? link(t.student.url, t.student.name) : make('span', '', '—'));
            tr.appendChild(studentTd);

            const typeTd = make('td');
            typeTd.appendChild(make('span', `badge ${t.type_badge}`, t.type_label));
            tr.appendChild(typeTd);

            tr.appendChild(make('td', 'fw-bold ' + (t.quantity > 0 ? 'text-success' : 'text-danger'), t.signed_quantity));
            tr.appendChild(make('td', '', t.reference_number || '—'));
            tr.appendChild(make('td', '', t.user || '—'));

            const actionTd = make('td', 'text-center');
            actionTd.appendChild(link(t.url, 'View', 'btn btn-sm btn-outline-primary'));
            tr.appendChild(actionTd);

            body.appendChild(tr);
        });
    }

    function setLastUpdated(text) {
        const el = byId('lastUpdated');
        if (el) el.textContent = text;
    }

    function setStatus(state, message) {
        const el = byId('pollStatus');
        if (!el) return;

        const map = {
            loading: ['bg-secondary', 'Updating...'],
            ok:      ['bg-success', 'Live'],
            error:   ['bg-danger', message || 'Offline - retrying'],
        };
        const [cls, label] = map[state];
        el.className = `badge ${cls}`;
        el.textContent = label;
    }

    // ---------------------------------------------------------------- fetch

    async function fetchJson(url) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store',
        });

        if (response.status === 401 || response.status === 419) {
            // session expired -> go back to login
            window.location.href = cfg.loginUrl || '/login';
            throw new Error('Session expired');
        }
        if (!response.ok) throw new Error('Server error (' + response.status + ')');

        return response.json();
    }

    async function refresh() {
        if (inFlight) return;
        inFlight = true;
        setStatus('loading');

        try {
            const [stats, low, recent] = await Promise.all([
                fetchJson(cfg.statsUrl),
                fetchJson(cfg.lowLoadUrl),
                fetchJson(cfg.recentUrl),
            ]);

            renderStats(stats.data);
            renderLowLoad(low.data);
            renderRecent(recent.data);

            setLastUpdated(stats.meta && stats.meta.generated_at_human
                ? stats.meta.generated_at_human
                : new Date().toLocaleString());
            setStatus('ok');
        } catch (error) {
            console.error('Dashboard refresh failed:', error);
            setStatus('error', 'Offline - retrying');
        } finally {
            inFlight = false;
        }
    }

    // ---------------------------------------------------------------- timer

    function startPolling() {
        stopPolling();
        timerId = setInterval(refresh, INTERVAL);
    }

    function stopPolling() {
        if (timerId) clearInterval(timerId);
        timerId = null;
    }

    document.addEventListener('DOMContentLoaded', function () {
        // remember the server-rendered numbers so the first change animates from them
        STAT_TARGETS.forEach(({ id }) => {
            const el = byId(id);
            if (el) el.dataset.value = String(parseNumber(el.textContent));
        });

        const refreshBtn = byId('refreshNow');
        if (refreshBtn) refreshBtn.addEventListener('click', refresh);

        // Pause when the tab is hidden, catch up when it is visible again
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopPolling();
            } else {
                refresh();
                startPolling();
            }
        });

        setStatus('ok');
        startPolling();
    });
})();
