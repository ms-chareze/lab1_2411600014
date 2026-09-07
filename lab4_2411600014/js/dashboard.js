let categoryChartInstance = null;
let statusChartInstance = null;
let topProductsChartInstance = null;
let simulationInterval = null;

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Session & Welcome Header
    const currentUser = localStorage.getItem('currentUser') || 'admin';
    const welcomeUserEl = document.getElementById('welcomeUser');
    const greetingHeaderEl = document.getElementById('greetingHeader');

    if (welcomeUserEl) welcomeUserEl.textContent = `Welcome, ${currentUser}`;
    if (greetingHeaderEl) greetingHeaderEl.textContent = `Good Evening, ${currentUser}!`;

    // 2. Working Logout Redirects
    const handleLogout = (e) => {
        e.preventDefault();
        localStorage.removeItem('currentUser');
        window.location.href = 'index.html';
    };

    const logoutBtn = document.getElementById('logoutBtn');
    const sidebarLogout = document.getElementById('sidebarLogout');
    if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);
    if (sidebarLogout) sidebarLogout.addEventListener('click', handleLogout);

    // 3. Load product dataset
    await DataManager.loadProducts();

    // Helper: Highlight matching search terms
    function highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark class="p-0 bg-warning">$1</mark>');
    }

    // 4. Render Chart.js
    function renderCharts(filteredProducts) {
        // Chart 1: Value by Category
        const categoryValues = {};
        filteredProducts.forEach(p => {
            categoryValues[p.category] = (categoryValues[p.category] || 0) + (p.price * p.quantity);
        });

        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        if (categoryChartInstance) categoryChartInstance.destroy();
        categoryChartInstance = new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(categoryValues),
                datasets: [{
                    label: 'Value ($)',
                    data: Object.values(categoryValues),
                    backgroundColor: '#9d80e4'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Chart 2: Stock Status Distribution
        const statusCounts = { "In Stock": 0, "Low Stock": 0, "Out of Stock": 0 };
        filteredProducts.forEach(p => {
            const status = DataManager.getStatus(p.quantity, p.minStock);
            statusCounts[status]++;
        });

        const statusCtx = document.getElementById('statusChart').getContext('2d');
        if (statusChartInstance) statusChartInstance.destroy();
        statusChartInstance = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(statusCounts),
                datasets: [{
                    data: Object.values(statusCounts),
                    backgroundColor: ['#198754', '#ffc107', '#dc3545']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Chart 3: Top 5 Products by Total Value
        const sortedProducts = [...filteredProducts]
            .sort((a, b) => (b.price * b.quantity) - (a.price * a.quantity))
            .slice(0, 5);

        const topCtx = document.getElementById('topProductsChart').getContext('2d');
        if (topProductsChartInstance) topProductsChartInstance.destroy();
        topProductsChartInstance = new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: sortedProducts.map(p => p.name),
                datasets: [{
                    label: 'Total Value ($)',
                    data: sortedProducts.map(p => p.price * p.quantity),
                    backgroundColor: '#641246'
                }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
        });
    }

    // 5. Update UI Stats, Table & Low Stock Alerts
    function updateDashboard() {
        const search = document.getElementById('searchInput').value;
        const category = document.getElementById('categoryFilter').value;
        const stockStatus = document.getElementById('stockFilter').value;
        const minPrice = parseFloat(document.getElementById('minPrice').value);
        const maxPrice = parseFloat(document.getElementById('maxPrice').value);

        const filtered = DataManager.getFilteredProducts(search, category, stockStatus, minPrice, maxPrice);

        // Update Key Metrics Cards
        let totalVal = 0;
        let totalQty = 0;
        let lowStockCount = 0;

        filtered.forEach(p => {
            totalVal += p.price * p.quantity;
            totalQty += p.quantity;
            if (p.quantity <= p.minStock) lowStockCount++;
        });

        document.getElementById('totalProducts').textContent = filtered.length;
        document.getElementById('totalValue').textContent = `$${totalVal.toFixed(2)}`;
        document.getElementById('lowStockAlerts').textContent = lowStockCount;
        document.getElementById('totalQuantity').textContent = totalQty;

        // Render Data Table
        const tbody = document.getElementById('inventoryTableBody');
        tbody.innerHTML = '';
        filtered.forEach(p => {
            const status = DataManager.getStatus(p.quantity, p.minStock);
            let badgeClass = 'bg-success';
            let rowClass = '';

            // Highlight low stock / out of stock rows in table
            if (status === 'Low Stock') {
                badgeClass = 'bg-warning text-dark';
                rowClass = 'table-warning';
            } else if (status === 'Out of Stock') {
                badgeClass = 'bg-danger';
                rowClass = 'table-danger';
            }

            const tr = document.createElement('tr');
            if (rowClass) tr.classList.add(rowClass);

            // Highlight search matching text in SKU and Name
            const highlightedSku = highlightText(p.sku, search);
            const highlightedName = highlightText(p.name, search);

            tr.innerHTML = `
                <td>${highlightedSku}</td>
                <td>${highlightedName}</td>
                <td>${p.category}</td>
                <td>$${p.price.toFixed(2)}</td>
                <td>${p.quantity}</td>
                <td><span class="badge ${badgeClass}">${status}</span></td>
                <td>$${(p.price * p.quantity).toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });

        // Update Low Stock Banner List
        const lowStockItems = DataManager.products.filter(p => p.quantity <= p.minStock);
        document.getElementById('lowStockCountText').textContent = lowStockItems.length;
        const alertList = document.getElementById('lowStockList');
        alertList.innerHTML = '';
        lowStockItems.forEach(p => {
            const li = document.createElement('li');
            li.textContent = `${p.name} (SKU: ${p.sku}) - Stock: ${p.quantity} / Min: ${p.minStock}`;
            alertList.appendChild(li);
        });

        // Update Chart Displays
        renderCharts(filtered);
    }

    // 6. Bind Input Controls
    ['searchInput', 'categoryFilter', 'stockFilter', 'minPrice', 'maxPrice'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateDashboard);
    });

    // Reset Filters Button
    const resetBtn = document.getElementById('resetFilters');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = 'All';
            document.getElementById('stockFilter').value = 'All';
            document.getElementById('minPrice').value = '';
            document.getElementById('maxPrice').value = '';
            updateDashboard();
        });
    }

    // Export Button
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            const stockStatus = document.getElementById('stockFilter').value;
            const minPrice = parseFloat(document.getElementById('minPrice').value);
            const maxPrice = parseFloat(document.getElementById('maxPrice').value);

            const filtered = DataManager.getFilteredProducts(search, category, stockStatus, minPrice, maxPrice);
            DataManager.exportToCSV(filtered);
        });
    }

    // Live Data Simulation Toggle
    const simToggle = document.getElementById('liveSimulationToggle');
    if (simToggle) {
        simToggle.addEventListener('change', (e) => {
            if (e.target.checked) {
                simulationInterval = setInterval(() => {
                    const randomIndex = Math.floor(Math.random() * DataManager.products.length);
                    const change = Math.floor(Math.random() * 5) - 2;
                    DataManager.products[randomIndex].quantity = Math.max(0, DataManager.products[randomIndex].quantity + change);
                    updateDashboard();
                }, 3000);
            } else {
                clearInterval(simulationInterval);
            }
        });
    }

    // Initial Dashboard Execution
    updateDashboard();
});