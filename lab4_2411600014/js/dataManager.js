const DataManager = {
    products: [],

    // Fetch data from api/products.php with local fallback
    async loadProducts() {
        try {
            const response = await fetch('api/products.php');
            if (!response.ok) throw new Error('API request failed');
            this.products = await response.json();
        } catch (error) {
            console.warn('Could not connect to api/products.php. Using fallback dataset.', error);
            this.products = [
                { sku: "PROD-001", name: "Wireless Ergonomic Mouse", category: "Electronics", price: 29.99, quantity: 45, minStock: 10 },
                { sku: "PROD-002", name: "Mechanical RGB Keyboard", category: "Electronics", price: 89.99, quantity: 8, minStock: 15 },
                { sku: "PROD-003", name: "Standing Desk Converter", category: "Furniture", price: 199.99, quantity: 12, minStock: 5 },
                { sku: "PROD-004", name: "Ergonomic Mesh Chair", category: "Furniture", price: 249.99, quantity: 3, minStock: 5 },
                { sku: "PROD-005", name: "USB-C Multi-Port Hub", category: "Electronics", price: 39.99, quantity: 0, minStock: 10 },
                { sku: "PROD-006", name: "27-inch 4K Monitor", category: "Electronics", price: 349.99, quantity: 18, minStock: 5 },
                { sku: "PROD-007", name: "Noise Canceling Headphones", category: "Electronics", price: 179.99, quantity: 50, minStock: 12 }
            ];
        }
        return this.products;
    },

    // Get Stock Status
    getStatus(quantity, minStock) {
        if (quantity === 0) return "Out of Stock";
        if (quantity <= minStock) return "Low Stock";
        return "In Stock";
    },

    // Filter Logic
    getFilteredProducts(search = "", category = "All", stockStatus = "All", minPrice = 0, maxPrice = Infinity) {
        return this.products.filter(p => {
            const matchesSearch = p.name.toLowerCase().includes(search.toLowerCase()) || 
                                  p.sku.toLowerCase().includes(search.toLowerCase());
            const matchesCategory = category === "All" || p.category === category;
            
            const status = this.getStatus(p.quantity, p.minStock);
            const matchesStatus = stockStatus === "All" || status === stockStatus;

            const matchesMinPrice = isNaN(minPrice) || minPrice === 0 || p.price >= minPrice;
            const matchesMaxPrice = isNaN(maxPrice) || maxPrice === Infinity || p.price <= maxPrice;

            return matchesSearch && matchesCategory && matchesStatus && matchesMinPrice && matchesMaxPrice;
        });
    },

    // CSV Download Functionality
    exportToCSV(data) {
        const headers = ["SKU", "Name", "Category", "Price", "Quantity", "Status", "Total Value"];
        const rows = data.map(p => [
            p.sku,
            `"${p.name}"`,
            p.category,
            p.price.toFixed(2),
            p.quantity,
            this.getStatus(p.quantity, p.minStock),
            (p.price * p.quantity).toFixed(2)
        ]);

        const csvContent = "data:text/csv;charset=utf-8," + 
            [headers.join(","), ...rows.map(e => e.join(","))].join("\n");

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "inventory_report.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};