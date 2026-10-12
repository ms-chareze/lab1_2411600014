document.addEventListener("DOMContentLoaded", async () => { // Waits for the HTML document to fully load before running the asynchronous script
    let loggedUser = localStorage.getItem("user") || "admin"; // Retrieves the logged-in user from browser storage, defaulting to "admin" if none exists
    let userRole = loggedUser === "student" ? "student" : "admin"; // Determines the user role based on the username
    
    if (document.getElementById("welcomeUser")) { // Checks if the welcome user element exists on the page
        document.getElementById("welcomeUser").textContent = `Welcome, ${loggedUser}`; // Sets the welcome text to display the current username
    }
    if (document.getElementById("roleBadge")) { // Checks if the role badge element exists on the page
        document.getElementById("roleBadge").textContent = userRole === "admin" ? "Admin Mode" : "Student View (Read-Only)"; // Displays the appropriate role label
    }

    if (userRole === "student") { // Checks if the current role is student
        document.getElementById("adminActionButtons")?.remove(); // Removes admin action buttons if they exist
        document.getElementById("actionHeader")?.remove(); // Removes action headers for students
    }

    updateGreeting(loggedUser); // Calls function to display a time-based greeting for the user
    await DataManager.loadStudents(); // Waits for student data to load from the data manager

    let courseChart, statusChart, topStudentsChart; // Declares variables to hold chart instances
    initCharts(); // Initializes all dashboard charts
    renderDashboard(); // Renders the main dashboard UI components
    setupEventListeners(); // Sets up all user interaction event listeners

    function renderDashboard() { // Defines the function that updates and renders all dashboard elements
        const searchQuery = document.getElementById("searchInput")?.value || ""; // Gets the current search input value
        const courseValue = document.getElementById("courseFilter   ")?.value || "All"; // Gets the selected course filter value
        const statusValue = document.getElementById("statusFilter")?.value || "All"; // Gets the selected status filter value
        const termValue = document.getElementById("termFilter")?.value || "All"; // Gets the selected academic term filter value

        DataManager.academicTerm = termValue; // Updates the data manager's active academic term
        const filteredData = DataManager.filterAndSortStudents(searchQuery, courseValue, statusValue, termValue); // Filters and sorts student data based on user criteria
        const paginationResult = DataManager.paginate(filteredData, DataManager.currentPage); // Applies pagination to the filtered student data

        updateMetrics(termValue); // Updates statistical summary metric cards
        renderTable(paginationResult.paginatedData); // Renders the student table rows for the current page
        renderPaginationUI(paginationResult); // Renders the pagination navigation controls
        renderAlerts(); // Renders warning alerts for students needing attention
        updateCharts(filteredData); // Updates the data charts with filtered information
    }

    function updateMetrics(term) { // Defines the function to update dashboard summary numbers
        const stats = DataManager.getStatistics(term); // Retrieves statistics for the specified academic term
        document.getElementById("totalStudents").textContent = stats.totalStudents; // Updates total student count display
        document.getElementById("averageGPA").textContent = stats.avgGPA; // Updates average GPA display
        document.getElementById("academicWarnings").textContent = stats.academicWarnings; // Updates academic warning count display
        document.getElementById("totalUnits").textContent = stats.totalUnits; // Updates total enrolled units display
    }

    function renderTable(data) { // Defines the function to populate the student table
        const tbody = document.getElementById("studentTableBody"); // Gets the table body element
        tbody.innerHTML = ""; // Clears existing table rows

        if (data.length === 0) { // Checks if there are no student records found
            const colSpan = userRole === "admin" ? 7 : 6; // Determines column span depending on user role
            tbody.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-4 text-muted">No student records found matching filter criteria.</td></tr>`; // Displays a 'no records found' message
            return; // Exits the function early
        }

        data.forEach(s => { // Loops through each student record in the data array
            let statusBadge = 'bg-success'; // Defaults the status badge class to green (Regular)
            if (s.status === 'Warning') statusBadge = 'bg-warning text-dark'; // Changes badge to yellow if status is Warning
            if (s.status === 'Probation') statusBadge = 'bg-danger'; // Changes badge to red if status is Probation

            const tr = document.createElement("tr"); // Creates a new table row element for the student
            tr.innerHTML = `
                <td class="fw-bold px-3">${s.studentId}</td>
                <td>${s.name}</td>
                <td>${s.course}</td>
                <td class="fw-bold">${Number(s.gpa).toFixed(2)}</td>
                <td>${s.enrolledUnits}</td>
                <td><span class="badge ${statusBadge}">${s.status}</span></td>
                ${userRole === "admin" ? `
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary py-0 px-2 edit-btn" data-id="${s.studentId}">Edit</button>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2 delete-btn" data-id="${s.studentId}">Delete</button>
                </td>` : ''}
            `; // Populates the row cells with student details and action buttons if admin

            tr.addEventListener("click", (e) => { // Adds click event listener to the row for viewing details
                if (!e.target.closest('button')) { // Checks if the click was not on an action button
                    showStudentDetails(s); // Opens the student detail breakdown modal
                }
            });

            tbody.appendChild(tr); // Appends the completed row to the table body
        });

        if (userRole === "admin") { // Checks if user is an admin to attach record management buttons
            document.querySelectorAll(".edit-btn").forEach(btn => { // Loops through all edit buttons
                btn.addEventListener("click", (e) => { // Adds click listener to handle editing
                    e.stopPropagation(); // Prevents the row click event from firing
                    openEditModal(btn.getAttribute("data-id")); // Opens the edit modal for the specific student ID
                });
            });
            document.querySelectorAll(".delete-btn").forEach(btn => { // Loops through all delete buttons
                btn.addEventListener("click", (e) => { // Adds click listener to handle deletion
                    e.stopPropagation(); // Prevents row click event from triggering
                    confirmDelete(btn.getAttribute("data-id")); // Prompts user to confirm student deletion
                });
            });
        }
    }

    function renderPaginationUI(pagResult) { // Defines the function to build pagination buttons and info text
        document.getElementById("paginationInfo").textContent = 
            `Showing ${pagResult.totalEntries === 0 ? 0 : pagResult.startIndex} to ${pagResult.endIndex} of ${pagResult.totalEntries} entries`; // Updates pagination range text info
        
        const ul = document.getElementById("paginationControls"); // Gets the pagination list container element
        ul.innerHTML = ""; // Clears existing pagination controls

        const prevLi = document.createElement("li"); // Creates the 'Previous' pagination item element
        prevLi.className = `page-item ${DataManager.currentPage === 1 ? 'disabled' : ''}`; // Disables button if on the first page
        prevLi.innerHTML = `<button class="page-link">Previous</button>`; // Sets button HTML markup
        prevLi.addEventListener("click", () => { // Adds click listener to go to the previous page
            if (DataManager.currentPage > 1) { // Checks if current page is greater than one
                DataManager.currentPage--; // Decrements current page number
                renderDashboard(); // Re-renders the dashboard
            }
        });
        ul.appendChild(prevLi); // Appends the 'Previous' button to the pagination list

        for (let i = 1; i <= pagResult.totalPages; i++) { // Loops to create numbered page items
            const li = document.createElement("li"); // Creates list item for a page number
            li.className = `page-item ${DataManager.currentPage === i ? 'active' : ''}`; // Highlights current active page item
            li.innerHTML = `<button class="page-link">${i}</button>`; // Sets page number text
            li.addEventListener("click", () => { // Adds click listener to switch to that specific page
                DataManager.currentPage = i; // Sets current page to selected index
                renderDashboard(); // Re-renders the dashboard
            });
            ul.appendChild(li); // Appends page number item to the pagination list
        }

        const nextLi = document.createElement("li"); // Creates the 'Next' pagination item element
        nextLi.className = `page-item ${DataManager.currentPage === pagResult.totalPages ? 'disabled' : ''}`; // Disables button if on the last page
        nextLi.innerHTML = `<button class="page-link">Next</button>`; // Sets button HTML markup
        nextLi.addEventListener("click", () => { // Adds click listener to go to the next page
            if (DataManager.currentPage < pagResult.totalPages) { // Checks if current page is less than total pages
                DataManager.currentPage++; // Increments current page number
                renderDashboard(); // Re-renders the dashboard
            }
        });
        ul.appendChild(nextLi); // Appends the 'Next' button to the pagination list
    }

    function renderAlerts() { // Defines function to check and display academic warning notifications
        const warningStudents = DataManager.getStudents().filter(s => s.status === 'Warning' || s.status === 'Probation'); // Filters students with warning or probation status
        const alertBox = document.getElementById("academicWarningAlert"); // Gets the warning alert container element
        const alertList = document.getElementById("warningList"); // Gets the list element for warning notifications
        const alertCount = document.getElementById("warningCountText"); // Gets the text element tracking warning counts

        if (warningStudents.length > 0) { // Checks if there are students with warnings
            alertBox.classList.remove("d-none"); // Displays the alert box by removing the hidden class
            alertCount.textContent = warningStudents.length; // Updates the count text
            alertList.innerHTML = warningStudents.map(s => `<li><strong>${s.name}</strong> (${s.course}) - GPA: ${Number(s.gpa).toFixed(2)} [${s.status}]</li>`).join(''); // Populates list of students in warning status
        } else {
            alertBox.classList.add("d-none"); // Hides the alert box if no warning students exist
        }
    }

    function initCharts() { // Defines function to initialize Chart.js graphs
        const ctxCourse = document.getElementById("courseChart").getContext("2d"); // Gets canvas context for course chart
        courseChart = new Chart(ctxCourse, { // Creates bar chart for course distribution
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'Enrolled Students', data: [], backgroundColor: '#A084DC' }] },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: {
                    x: { title: { display: true, text: 'Courses' } },
                    y: { title: { display: true, text: 'Student Count' }, beginAtZero: true }
                }
            }
        });

        const ctxStatus = document.getElementById("statusChart").getContext("2d"); // Gets canvas context for status chart
        statusChart = new Chart(ctxStatus, { // Creates doughnut chart for student statuses
            type: 'doughnut',
            data: { 
                labels: [], 
                datasets: [{ 
                    data: [], 
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'] 
                }] 
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const ctxTop = document.getElementById("topStudentsChart").getContext("2d"); // Gets canvas context for top students chart
        topStudentsChart = new Chart(ctxTop, { // Creates horizontal bar chart for top-performing students
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'GPA', data: [], backgroundColor: '#612D53' }] },
            options: { 
                indexAxis: 'y', 
                responsive: true, 
                maintainAspectRatio: false, 
                scales: { 
                    x: { max: 4.0, title: { display: true, text: 'Grade Point Average (GPA)' } },
                    y: { title: { display: true, text: 'Top Students' } }
                } 
            }
        });
    }

    function updateCharts(data) { // Defines function to update charts with fresh data
        const courseCounts = {}; // Initializes object to tally student counts per course
        data.forEach(s => courseCounts[s.course] = (courseCounts[s.course] || 0) + 1); // Counts students per course
        courseChart.data.labels = Object.keys(courseCounts); // Sets course chart labels
        courseChart.data.datasets[0].data = Object.values(courseCounts); // Sets course chart dataset values
        courseChart.update(); // Refreshes course chart rendering

        const statusCounts = { Regular: 0, Warning: 0, Probation: 0 }; // Initializes object to tally status counts
        data.forEach(s => statusCounts[s.status] = (statusCounts[s.status] || 0) + 1); // Counts students per status category
        statusChart.data.labels = Object.keys(statusCounts); // Sets status chart labels
        statusChart.data.datasets[0].data = Object.values(statusCounts); // Sets status chart dataset values
        statusChart.update(); // Refreshes status chart rendering

        const sorted = [...data].sort((a, b) => b.gpa - a.gpa).slice(0, 5); // Sorts students by GPA descending and grabs top 5
        topStudentsChart.data.labels = sorted.map(s => s.name); // Sets top student names as labels
        topStudentsChart.data.datasets[0].data = sorted.map(s => s.gpa); // Sets top student GPAs as data values
        topStudentsChart.update(); // Refreshes top students chart rendering
    }

    function showStudentDetails(student) { // Defines function to display student details in a modal
        const modalTitle = document.getElementById("detailModalTitle"); // Gets modal title element
        const modalBody = document.getElementById("detailModalBody"); // Gets modal body element

        modalTitle.textContent = `${student.name} (${student.studentId})`; // Sets modal title text with name and ID
        
        let gradesHTML = `<p class="text-muted">No individual subject grades recorded.</p>`; // Default fallback text for grades
        if (student.grades && student.grades.length > 0) { // Checks if individual subject grades exist
            gradesHTML = `
                <table class="table table-sm table-bordered mt-2">
                    <thead class="table-light">
                        <tr><th>Subject</th><th>Midterm</th><th>Final</th><th>Remarks</th></tr>
                    </thead>
                    <tbody>
                        ${student.grades.map(g => `<tr><td>${g.subject}</td><td>${g.midterm}</td><td>${g.final}</td><td>${g.remarks}</td></tr>`).join('')}
                    </tbody>
                </table>
            `; // Builds HTML table for subject grades
        }

        modalBody.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-6"><strong>Course:</strong> ${student.course}</div>
                <div class="col-md-6"><strong>Term:</strong> ${student.term}</div>
                <div class="col-md-6"><strong>Cumulative GPA:</strong> ${Number(student.gpa).toFixed(2)}</div>
                <div class="col-md-6"><strong>Enrolled Units:</strong> ${student.enrolledUnits}</div>
                <div class="col-md-6"><strong>Status:</strong> <span class="badge bg-secondary">${student.status}</span></div>
            </div>
            <h6 class="fw-bold mt-4" style="color: var(--theme-text);">Subject Grade Breakdown</h6>
            ${gradesHTML}
        `; // Populates modal body layout with student attributes and grades

        new bootstrap.Modal(document.getElementById("detailModal")).show(); // Triggers display of the Bootstrap detail modal
    }

    function openEditModal(studentId) { // Defines function to open student record edit modal
        const student = DataManager.getStudents().find(s => s.studentId === studentId); // Finds student record by ID
        if (!student) return; // Exits if student is not found

        document.getElementById("studentModalTitle").textContent = "Edit Student Record"; // Updates modal header title
        document.getElementById("formMode").value = "edit"; // Sets form mode hidden input to edit
        document.getElementById("modalStudentId").value = student.studentId; // Populates student ID field
        document.getElementById("modalStudentId").setAttribute("readonly", true); // Makes ID field read-only during edits
        document.getElementById("modalName").value = student.name; // Populates student name input
        document.getElementById("modalCourse").value = student.course; // Populates course select input
        document.getElementById("modalGpa").value = student.gpa; // Populates GPA input
        document.getElementById("modalUnits").value = student.enrolledUnits; // Populates units input
        document.getElementById("modalStatus").value = student.status; // Populates status select input

        new bootstrap.Modal(document.getElementById("studentModal")).show(); // Shows the student form modal
    }

    function confirmDelete(studentId) { // Defines function to handle student deletion confirmation
        if (confirm(`Are you sure you want to delete student ID ${studentId}?`)) { // Prompts browser confirmation dialog
            DataManager.deleteStudent(studentId); // Deletes the student record from data manager
            renderDashboard(); // Re-renders the dashboard UI
        }
    }

    function setupEventListeners() { // Defines function to bind UI event listeners
        document.getElementById("searchInput").addEventListener("input", () => { DataManager.currentPage = 1; renderDashboard(); }); // Resets page and updates on search input
        document.getElementById("courseFilter").addEventListener("change", () => { DataManager.currentPage = 1; renderDashboard(); }); // Resets page and updates on course filter change
        document.getElementById("statusFilter").addEventListener("change", () => { DataManager.currentPage = 1; renderDashboard(); }); // Resets page and updates on status filter change
        document.getElementById("termFilter").addEventListener("change", () => { DataManager.currentPage = 1; renderDashboard(); }); // Resets page and updates on term filter change

        document.getElementById("resetFilters").addEventListener("click", () => { // Binds handler to reset filter controls button
            document.getElementById("searchInput").value = ""; // Clears search text
            document.getElementById("courseFilter").value = "All"; // Resets course filter to All
            document.getElementById("statusFilter").value = "All"; // Resets status filter to All
            document.getElementById("termFilter").value = "1st Semester S.Y. 2026-2027"; // Resets term filter to default term
            DataManager.currentPage = 1; // Resets pagination current page
            renderDashboard(); // Re-renders dashboard
        });

        document.querySelectorAll("th.sortable").forEach(th => { // Loops through sortable table header cells
            th.addEventListener("click", () => { // Adds click listener for column sorting
                const col = th.getAttribute("data-sort"); // Gets column sort key attribute
                if (DataManager.sortColumn === col) { // Checks if column is already being sorted
                    DataManager.sortDirection = DataManager.sortDirection === 'asc' ? 'desc' : 'asc'; // Toggles sort direction
                } else {
                    DataManager.sortColumn = col; // Sets new active sort column
                    DataManager.sortDirection = 'asc'; // Defaults sort direction to ascending
                }
                renderDashboard(); // Re-renders dashboard with sorted data
            });
        });

        document.getElementById("addNewStudentBtn")?.addEventListener("click", () => { // Binds handler for adding new students
            document.getElementById("studentModalTitle").textContent = "Add New Student"; // Sets modal title
            document.getElementById("formMode").value = "add"; // Sets form mode hidden input to add
            document.getElementById("studentForm").reset(); // Clears all form inputs
            document.getElementById("modalStudentId").removeAttribute("readonly"); // Ensures student ID field is editable
        });

        document.getElementById("studentForm").addEventListener("submit", (e) => { // Handles student form submission (add/edit)
            e.preventDefault(); // Prevents default browser form submission refresh
            const mode = document.getElementById("formMode").value; // Gets current form mode (add or edit)
            const studentId = document.getElementById("modalStudentId").value.trim(); // Gets student ID value
            const name = document.getElementById("modalName").value.trim(); // Gets name value
            const course = document.getElementById("modalCourse").value; // Gets course selection value
            const gpa = parseFloat(document.getElementById("modalGpa").value); // Parses GPA value as float number
            const enrolledUnits = parseInt(document.getElementById("modalUnits").value); // Parses enrolled units as integer
            const status = document.getElementById("modalStatus").value; // Gets status value
            const term = document.getElementById("termFilter").value; // Gets current term filter value

            if (mode === "add") { // Checks if mode is set to add
                if (DataManager.getStudents().some(s => s.studentId === studentId)) { // Checks if student ID already exists
                    alert("Student ID already exists!"); // Warns user of duplicate ID
                    return; // Exits early
                }
                DataManager.addStudent({ studentId, name, course, gpa, enrolledUnits, status, term }); // Adds new student record
            } else {
                DataManager.updateStudent(studentId, { name, course, gpa, enrolledUnits, status }); // Updates existing student record
            }

            bootstrap.Modal.getInstance(document.getElementById("studentModal")).hide(); // Hides modal window
            renderDashboard(); // Re-renders dashboard to reflect modifications
        });

        document.getElementById("exportBtn").addEventListener("click", () => { // Binds handler for exporting CSV data
            const filtered = DataManager.filterAndSortStudents(
                document.getElementById("searchInput").value,
                document.getElementById("courseFilter").value,
                document.getElementById("statusFilter").value,
                document.getElementById("termFilter").value
            ); // Gets filtered data subset
            DataManager.exportToCSV(filtered); // Triggers CSV file download export
        });

        document.getElementById("csvFileInput").addEventListener("change", (e) => { // Binds handler for bulk CSV imports
            const file = e.target.files[0]; // Gets selected file
            if (!file) return; // Exits if no file is selected
            const reader = new FileReader(); // Creates file reader instance
            reader.onload = function(evt) { // Handles file load completion
                const count = DataManager.importCSV(evt.target.result); // Imports CSV text and gets imported count
                alert(`Successfully imported ${count} new student records!`); // Displays success alert notification
                renderDashboard(); // Re-renders dashboard
                e.target.value = ""; // Resets file input value
            };
            reader.readAsText(file); // Reads file contents as text string
        });

        document.getElementById("navDashboard").addEventListener("click", (e) => { // Binds handler for dashboard navigation link
            e.preventDefault(); // Prevents default link action
            location.reload(); // Reloads page to show main dashboard
        });

        document.getElementById("navAudit").addEventListener("click", (e) => { // Binds handler for audit log view link
            e.preventDefault(); // Prevents default link action
            const logs = DataManager.getAuditLogs(); // Retrieves administrative activity audit logs
            const area = document.getElementById("mainContentArea"); // Gets main content container area
            area.innerHTML = `
                <h3 class="fw-bold mb-3" style="color: var(--theme-text);">Administrative Audit Trail Logs</h3>
                <div class="card shadow-sm border-0 p-3 bg-white">
                    <ul class="list-group list-group-flush">
                        ${logs.length === 0 ? '<li class="list-group-item text-muted">No activity logs recorded yet.</li>' : 
                            logs.map(l => `<li class="list-group-item d-flex justify-content-between align-items-center"><span>${l.text}</span><small class="text-muted">${l.time}</small></li>`).join('')}
                    </ul>
                    <button class="btn btn-secondary btn-sm mt-3 w-25" onclick="location.reload()">Back to Dashboard</button>
                </div>
            `; // Replaces content area with rendered audit log trail
        });

        document.getElementById("navProfile").addEventListener("click", (e) => { // Binds handler for profile settings view link
            e.preventDefault(); // Prevents default link action
            const area = document.getElementById("mainContentArea"); // Gets main content container area
            area.innerHTML = `
                <h3 class="fw-bold mb-3" style="color: var(--theme-text);">Admin Profile & Portal Settings</h3>
                <div class="card shadow-sm border-0 p-4 bg-white col-md-8">
                    <form id="settingsForm">
                        <div class="mb-3">
                            <label class="form-label small">Logged-in Administrator</label>
                            <input type="text" class="form-control form-control-sm" value="${loggedUser}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Default Grading Scale Threshold (Passing GPA)</label>
                            <input type="number" step="0.1" class="form-control form-control-sm" value="2.5">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Rows Per Page</label>
                            <select id="settingsRows" class="form-select form-select-sm">
                                <option value="5" ${DataManager.rowsPerPage === 5 ? 'selected':''}>5 Entries</option>
                                <option value="10" ${DataManager.rowsPerPage === 10 ? 'selected':''}>10 Entries</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="alert('Settings saved successfully!'); location.reload();">Save Preferences</button>
                        <button type="button" class="btn btn-secondary btn-sm ms-2" onclick="location.reload()">Back to Dashboard</button>
                    </form>
                </div>
            `; // Replaces content area with admin settings form layout
        });

        const handleLogout = () => { // Defines logout handler function
            localStorage.removeItem("user"); // Removes user from local storage
            localStorage.removeItem("isLoggedIn"); // Clears login status flag
            window.location.href = "index.html"; // Redirects browser back to login page
        };
        document.getElementById("logoutBtn")?.addEventListener("click", handleLogout); // Binds logout button handler
        document.getElementById("sidebarLogout")?.addEventListener("click", handleLogout); // Binds sidebar logout link handler
    }

    function updateGreeting(username) { // Defines function to generate time-specific greetings
        const greetingElement = document.getElementById("dashboardGreeting"); // Gets greeting text element
        if (!greetingElement) return; // Exits if element is missing

        const hour = new Date().getHours(); // Gets current local hour of the day
        let timeOfDay = "Good Morning"; // Defaults greeting to Good Morning
        if (hour >= 12 && hour < 17) timeOfDay = "Good Afternoon";
        else if (hour >= 17) timeOfDay = "Good Evening";

        greetingElement.textContent = `${timeOfDay}, ${username}!`; // Sets the final greeting text
    }
});