document.addEventListener("DOMContentLoaded", async () => {
    const loggedUser = localStorage.getItem("user") || "admin";
    if (document.getElementById("welcomeUser")) {
        document.getElementById("welcomeUser").textContent = `Welcome, ${loggedUser}`;
    }

    await DataManager.loadStudents();

    let courseChart, statusChart, topStudentsChart;

    initCharts();
    renderDashboard();
    setupEventListeners();

    function renderDashboard() {
        const searchQuery = document.getElementById("searchInput")?.value || "";
        const courseValue = document.getElementById("courseFilter")?.value || "All";
        const statusValue = document.getElementById("statusFilter")?.value || "All";

        const filteredData = DataManager.filterStudents(searchQuery, courseValue, statusValue);

        updateMetrics();
        renderTable(filteredData);
        renderAlerts();
        updateCharts(filteredData);
    }

    function updateMetrics() {
        const stats = DataManager.getStatistics();
        document.getElementById("totalStudents").textContent = stats.totalStudents;
        document.getElementById("averageGPA").textContent = stats.avgGPA;
        document.getElementById("academicWarnings").textContent = stats.academicWarnings;
        document.getElementById("totalUnits").textContent = stats.totalUnits;
    }

    function renderTable(data) {
        const tbody = document.getElementById("studentTableBody");
        tbody.innerHTML = "";

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-muted">No student records found matching filter criteria.</td></tr>`;
            return;
        }

        data.forEach(s => {
            let statusBadge = 'bg-success';
            if (s.status === 'Warning') statusBadge = 'bg-warning text-dark';
            if (s.status === 'Probation') statusBadge = 'bg-danger';

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td class="fw-bold px-3">${s.studentId}</td>
                <td>${s.name}</td>
                <td>${s.course}</td>
                <td class="fw-bold">${s.gpa.toFixed(2)}</td>
                <td>${s.enrolledUnits}</td>
                <td><span class="badge ${statusBadge}">${s.status}</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderAlerts() {
        const warningStudents = DataManager.getStudents().filter(s => s.status === 'Warning' || s.status === 'Probation');
        const alertBox = document.getElementById("academicWarningAlert");
        const alertList = document.getElementById("warningList");
        const alertCount = document.getElementById("warningCountText");

        if (warningStudents.length > 0) {
            alertBox.classList.remove("d-none");
            alertCount.textContent = warningStudents.length;
            alertList.innerHTML = warningStudents.map(s => `<li><strong>${s.name}</strong> (${s.course}) - GPA: ${s.gpa.toFixed(2)} [${s.status}]</li>`).join('');
        } else {
            alertBox.classList.add("d-none");
        }
    }

    function initCharts() {
        // Chart colors using Lab 3 purple palette
        const ctxCourse = document.getElementById("courseChart").getContext("2d");
        courseChart = new Chart(ctxCourse, {
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'Enrolled Students', data: [], backgroundColor: '#A084DC' }] },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const ctxStatus = document.getElementById("statusChart").getContext("2d");
        statusChart = new Chart(ctxStatus, {
            type: 'doughnut',
            data: { labels: [], datasets: [{ data: [], backgroundColor: ['#28a745', '#F9B2D7', '#dc3545'] }] },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const ctxTop = document.getElementById("topStudentsChart").getContext("2d");
        topStudentsChart = new Chart(ctxTop, {
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'GPA', data: [], backgroundColor: '#612D53' }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { max: 4.0 } } }
        });
    }

    function updateCharts(data) {
        const courseCounts = {};
        data.forEach(s => courseCounts[s.course] = (courseCounts[s.course] || 0) + 1);
        courseChart.data.labels = Object.keys(courseCounts);
        courseChart.data.datasets[0].data = Object.values(courseCounts);
        courseChart.update();

        const statusCounts = { Regular: 0, Warning: 0, Probation: 0 };
        data.forEach(s => statusCounts[s.status] = (statusCounts[s.status] || 0) + 1);
        statusChart.data.labels = Object.keys(statusCounts);
        statusChart.data.datasets[0].data = Object.values(statusCounts);
        statusChart.update();

        const sorted = [...data].sort((a, b) => b.gpa - a.gpa).slice(0, 5);
        topStudentsChart.data.labels = sorted.map(s => s.name);
        topStudentsChart.data.datasets[0].data = sorted.map(s => s.gpa);
        topStudentsChart.update();
    }

    function setupEventListeners() {
        document.getElementById("searchInput").addEventListener("input", renderDashboard);
        document.getElementById("courseFilter").addEventListener("change", renderDashboard);
        document.getElementById("statusFilter").addEventListener("change", renderDashboard);

        document.getElementById("resetFilters").addEventListener("click", () => {
            document.getElementById("searchInput").value = "";
            document.getElementById("courseFilter").value = "All";
            document.getElementById("statusFilter").value = "All";
            renderDashboard();
        });

        document.getElementById("exportBtn").addEventListener("click", () => {
            const courseValue = document.getElementById("courseFilter").value;
            const statusValue = document.getElementById("statusFilter").value;
            const searchQuery = document.getElementById("searchInput").value;
            const filtered = DataManager.filterStudents(searchQuery, courseValue, statusValue);
            DataManager.exportToCSV(filtered);
        });

        const handleLogout = () => {
            localStorage.removeItem("user");
            localStorage.removeItem("isLoggedIn");
            window.location.href = "index.html";
        };
        document.getElementById("logoutBtn")?.addEventListener("click", handleLogout);
        document.getElementById("sidebarLogout")?.addEventListener("click", handleLogout);

        let simInterval = null;
        document.getElementById("liveSimulationToggle").addEventListener("change", (e) => {
            if (e.target.checked) {
                simInterval = setInterval(() => {
                    const students = DataManager.getStudents();
                    if (students.length > 0) {
                        const randomIndex = Math.floor(Math.random() * students.length);
                        students[randomIndex].gpa = +(Math.random() * (4.00 - 1.50) + 1.50).toFixed(2);
                        if (students[randomIndex].gpa < 2.0) students[randomIndex].status = "Probation";
                        else if (students[randomIndex].gpa < 2.5) students[randomIndex].status = "Warning";
                        else students[randomIndex].status = "Regular";

                        renderDashboard();
                    }
                }, 4000);
            } else {
                clearInterval(simInterval);
            }
        });
    }
});