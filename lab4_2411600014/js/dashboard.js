<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 4 Enhanced Inventory Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Inventory Portal</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3" id="userDisplay">Welcome, Admin</span>
                <button class="btn btn-outline-light btn-sm" id="logoutBtn">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <main class="col-md-12 px-md-4 py-4">
                
                <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                    <h2 id="greeting">Dashboard Overview</h2>
                    <button class="btn btn-success" id="exportBtn">📥 Export CSV</button>
                </div>

                <!-- Low Stock Alert Banner -->
                <div id="alertSection" class="mb-4"></div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card p-3 shadow-sm border-start border-4 border-primary">
                            <span class="text-muted small">Total Products</span>
                            <h3 id="statTotalProducts" class="fw-bold mb-0">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3 shadow-sm border-start border-4 border-success">
                            <span class="text-muted small">Total Inventory Value</span>
                            <h3 id="statTotalValue" class="fw-bold mb-0">$0.00</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3 shadow-sm border-start border-4 border-danger">
                            <span class="text-muted small">Low Stock Alerts</span>
                            <h3 id="statLowStock" class="fw-bold mb-0 text-danger">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3 shadow-sm border-start border-4 border-info">
                            <span class="text-muted small">Categories</span>
                            <h3 id="statCategories" class="fw-bold mb-0">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card chart-card p-3">
                            <h6 class="fw-bold">Value by Category</h6>
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card chart-card p-3">
                            <h6 class="fw-bold">Stock Status Distribution</h6>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card chart-card p-3">
                            <h6 class="fw-bold">Top 5 Products by Value</h6>
                            <div class="chart-container">
                                <canvas id="topProductsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter & Search Controls -->
                <div class="card p-3 mb-4 shadow-sm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search by Name or SKU...">
                        </div>
                        <div class="col-md-3">
                            <select id="categoryFilter" class="form-select">
                                <option value="ALL">All Categories</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-select">
                                <option value="ALL">All Stock Statuses</option>
                                <option value="In Stock">In Stock</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button id="resetFiltersBtn" class="btn btn-outline-secondary w-100">Reset Filters</button>
                        </div>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Inventory Products</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SKU</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody"></tbody>
                        </table>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- JavaScript Module Loading -->
    <script src="js/dataManager.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>