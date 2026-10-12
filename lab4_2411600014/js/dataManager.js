const DataManager = { // Defines the DataManager object to manage students, logs, and data operations
    students: [], // Initializes an empty array to store student records
    auditLogs: [], // Initializes an empty array to store administrative action logs
    currentPage: 1, // Sets the default active pagination page to 1
    rowsPerPage: 5, // Sets the default number of rows displayed per page to 5
    sortColumn: 'name', // Sets the default column used for sorting to 'name'
    sortDirection: 'asc', // Sets the default sort direction to ascending
    academicTerm: '1st Semester S.Y. 2026-2027', // Sets the default active academic term

    async loadStudents() { // Defines an asynchronous function to load student records and logs
        const savedStudents = localStorage.getItem('portal_students'); // Retrieves saved student data from browser local storage
        const savedLogs = localStorage.getItem('portal_audit_logs'); // Retrieves saved audit logs from browser local storage
        
        if (savedStudents) { // Checks if student data exists in local storage
            this.students = JSON.parse(savedStudents); // Parses and assigns the saved student data to the students array
        } else { // Runs if no saved student data is found
            try { // Starts a try block to fetch data from the server API
                const response = await fetch('api/students.php'); // Sends an HTTP request to get students from the PHP backend
                const result = await response.json(); // Parses the response into JSON format
                if (result.status === 'success') { // Checks if the API returned a success status
                    this.students = result.data.map(s => this.enrichStudentGrades(s)); // Maps and enriches each student with grades
                } else { // Runs if the API status is not success
                    throw new Error('API Error'); // Throws a custom error
                }
            } catch (error) { // Catches any errors during the fetch process
                console.warn('API error, loading fallback data.', error); // Logs a warning message to the console
                this.students = [ // Assigns fallback mock student records if the API fails
                    { studentId: "STU-102", name: "Jane Doe", course: "BS Information Technology", gpa: 3.20, enrolledUnits: 18, status: "Regular", term: "1st Semester S.Y. 2026-2027", grades: [{subject: "IT 101", midterm: 3.0, final: 3.4, remarks: "Passed"}] }, // Fallback student 1
                    { studentId: "STU-103", name: "John Smith", course: "BS Computer Science", gpa: 2.40, enrolledUnits: 12, status: "Warning", term: "1st Semester S.Y. 2026-2027", grades: [{subject: "CS 101", midterm: 2.2, final: 2.6, remarks: "Needs Improvement"}] }, // Fallback student 2
                    { studentId: "STU-104", name: "Maria Santos", course: "BS Business Administration", gpa: 3.95, enrolledUnits: 24, status: "Regular", term: "1st Semester S.Y. 2026-2027", grades: [{subject: "BA 101", midterm: 3.9, final: 4.0, remarks: "Excellent"}] }, // Fallback student 3
                    { studentId: "STU-105", name: "Alex Johnson", course: "BS Information Technology", gpa: 1.80, enrolledUnits: 9, status: "Probation", term: "1st Semester S.Y. 2026-2027", grades: [{subject: "IT 102", midterm: 1.5, final: 2.1, remarks: "At Risk"}] } // Fallback student 4
                ]; // Ends fallback array
            } // Ends catch block
            this.saveToStorage(); // Saves the loaded or fallback data to local storage
        } // Ends else block

        if (savedLogs) { // Checks if saved audit logs exist in local storage
            this.auditLogs = JSON.parse(savedLogs); // Parses and assigns saved audit logs
        } // Ends if block
        return this.students; // Returns the current list of students
    }, // Ends loadStudents method

    saveToStorage() { // Defines function to save data into browser local storage
        localStorage.setItem('portal_students', JSON.stringify(this.students)); // Saves students array as a JSON string
        localStorage.setItem('portal_audit_logs', JSON.stringify(this.auditLogs)); // Saves audit logs array as a JSON string
    }, // Ends saveToStorage method

    logAction(action) { // Defines function to record user actions into audit logs
        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }); // Generates current formatted time string
        const user = localStorage.getItem("user") || "admin"; // Gets current logged-in user or defaults to admin
        this.auditLogs.unshift({ text: `${user} ${action} at ${timestamp}`, time: timestamp }); // Adds new log entry to the beginning of the array
        if (this.auditLogs.length > 20) this.auditLogs.pop(); // Keeps the log history size to a maximum of 20 items
        localStorage.setItem('portal_audit_logs', JSON.stringify(this.auditLogs)); // Updates audit logs in local storage
    }, // Ends logAction method

    enrichStudentGrades(student) { // Defines function to add mock subject grades if missing
        if (!student.grades) { // Checks if the student lacks grade details
            student.grades = [ // Assigns default mock subject grades
                { subject: "Core Subject 1", midterm: +(student.gpa + 0.1).toFixed(2), final: student.gpa, remarks: student.gpa >= 2.5 ? "Passed" : "Warning" }, // Core subject record
                { subject: "Advanced Elective", midterm: student.gpa, final: +(student.gpa - 0.1).toFixed(2), remarks: student.gpa >= 2.5 ? "Passed" : "Review Needed" } // Elective subject record
            ]; // Ends grades array
        } // Ends if block
        if (!student.term) student.term = "1st Semester S.Y. 2026-2027"; // Assigns default term if not defined
        return student; // Returns the enriched student object
    }, // Ends enrichStudentGrades method

    getStudents() { return this.students; }, // Returns the students array
    getAuditLogs() { return this.auditLogs; }, // Returns the audit logs array

    addStudent(newStudent) { // Defines function to add a new student record
        newStudent = this.enrichStudentGrades(newStudent); // Enriches the new student with default grades
        this.students.push(newStudent); // Pushes the new student into the students array
        this.saveToStorage(); // Saves updated data to local storage
        this.logAction(`added student record for ${newStudent.name}`); // Logs the add action
    }, // Ends addStudent method

    updateStudent(studentId, updatedData) { // Defines function to update an existing student record
        const index = this.students.findIndex(s => s.studentId === studentId); // Finds the array index of the student ID
        if (index !== -1) { // Checks if the student exists
            this.students[index] = { ...this.students[index], ...updatedData }; // Merges old data with updated data
            this.saveToStorage(); // Saves changes to local storage
            this.logAction(`updated records for ${this.students[index].name}`); // Logs the update action
        } // Ends if block
    }, // Ends updateStudent method

    deleteStudent(studentId) { // Defines function to delete a student record
        const student = this.students.find(s => s.studentId === studentId); // Finds the student object by ID
        if (student) { // Checks if the student exists
            this.students = this.students.filter(s => s.studentId !== studentId); // Filters out the student from the array
            this.saveToStorage(); // Saves changes to local storage
            this.logAction(`deleted student record (${student.name})`); // Logs the delete action
        } // Ends if block
    }, // Ends deleteStudent method

    getStatistics(term = "All") { // Defines function to calculate dashboard statistical numbers
        const filtered = term === "All" ? this.students : this.students.filter(s => s.term === term); // Filters students by academic term
        const totalStudents = filtered.length; // Counts total filtered students
        const totalUnits = filtered.reduce((acc, s) => acc + Number(s.enrolledUnits), 0); // Calculates total enrolled units
        const avgGPA = totalStudents > 0  // Checks if student count is greater than zero
            ? (filtered.reduce((acc, s) => acc + Number(s.gpa), 0) / totalStudents).toFixed(2)  // Calculates average GPA
            : "0.00"; // Returns 0.00 if no students exist
        const academicWarnings = filtered.filter(s => s.status === 'Warning' || s.status === 'Probation').length; // Counts students in warning or probation status

        return { totalStudents, totalUnits, avgGPA, academicWarnings }; // Returns the computed statistics object
    }, // Ends getStatistics method

    filterAndSortStudents(search = "", courseFilter = "All", statusFilter = "All", termFilter = "All") { // Defines function to search, filter, and sort student records
        let filtered = this.students.filter(s => { // Filters the students array based on criteria
            const matchesSearch = s.name.toLowerCase().includes(search.toLowerCase()) ||  // Checks if name matches search query
                                  s.studentId.toLowerCase().includes(search.toLowerCase()); // Or checks if student ID matches search query
            const matchesCourse = courseFilter === "All" || s.course === courseFilter; // Checks if course matches filter
            const matchesStatus = statusFilter === "All" || s.status === statusFilter; // Checks if status matches filter
            const matchesTerm = termFilter === "All" || s.term === termFilter; // Checks if term matches filter

            return matchesSearch && matchesCourse && matchesStatus && matchesTerm; // Returns true if all criteria match
        }); // Ends filter method

        filtered.sort((a, b) => { // Sorts the filtered students array
            let valA = a[this.sortColumn]; // Gets property value for first item
            let valB = b[this.sortColumn]; // Gets property value for second item
            if (typeof valA === 'string') { // Checks if values are strings
                valA = valA.toLowerCase(); // Converts string to lowercase for proper sorting
                valB = valB.toLowerCase(); // Converts string to lowercase for proper sorting
            } // Ends if block
            if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1; // Determines sort order if A is less than B
            if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1; // Determines sort order if A is greater than B
            return 0; // Returns 0 if values are equal
        }); // Ends sort method

        return filtered; // Returns the filtered and sorted array
    }, // Ends filterAndSortStudents method

    paginate(data, page) { // Defines function to handle data pagination
        this.currentPage = page; // Updates current active page
        const start = (page - 1) * this.rowsPerPage; // Calculates starting index for slice
        const end = start + this.rowsPerPage; // Calculates ending index for slice
        return { // Returns pagination result object
            paginatedData: data.slice(start, end), // Slices data array for current page view
            totalPages: Math.ceil(data.length / this.rowsPerPage) || 1, // Calculates total page count
            totalEntries: data.length, // Total number of entries
            startIndex: start + 1, // Starting entry display number
            endIndex: Math.min(end, data.length) // Ending entry display number
        }; // Ends return object
    }, // Ends paginate method

    exportToCSV(data) { // Defines function to export student data to a CSV file
        const headers = ["Student ID", "Name", "Course", "GPA", "Enrolled Units", "Academic Status", "Term"]; // Defines CSV table column headers
        const rows = data.map(s => [s.studentId, `"${s.name}"`, `"${s.course}"`, s.gpa.toFixed(2), s.enrolledUnits, s.status, `"${s.term}"`]); // Maps student records into CSV rows format
        const csvContent = "data:text/csv;charset=utf-8," + [headers.join(","), ...rows.map(e => e.join(","))].join("\n"); // Combines headers and rows into a single CSV string
        const encodedUri = encodeURI(csvContent); // Encodes the CSV data URI string
        const link = document.createElement("a"); // Creates a temporary anchor element
        link.setAttribute("href", encodedUri); // Sets the href attribute to the encoded CSV data
        link.setAttribute("download", "student_academic_records.csv"); // Sets the downloaded file name
        document.body.appendChild(link); // Appends the link to the document body
        link.click(); // Programmatically clicks the link to trigger file download
        document.body.removeChild(link); // Removes the temporary link from the DOM
        this.logAction("exported student records to CSV"); // Logs the CSV export action
    }, // Ends exportToCSV method

    importCSV(text) { // Defines function to import student records from uploaded CSV text
        const lines = text.split("\n"); // Splits CSV text into individual lines
        let addedCount = 0; // Initializes counter for successfully imported records
        for (let i = 1; i < lines.length; i++) { // Loops through each line starting after the header row
            const line = lines[i].trim(); // Trims whitespace from the current line
            if (!line) continue; // Skips iteration if line is empty
            const cols = line.split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/); // Splits CSV columns correctly handling quoted strings
            if (cols.length >= 6) { // Checks if minimum required columns exist
                const studentId = cols[0].replace(/"/g, '').trim(); // Extracts and cleans student ID
                const name = cols[1].replace(/"/g, '').trim(); // Extracts and cleans student name
                const course = cols[2].replace(/"/g, '').trim(); // Extracts and cleans course name
                const gpa = parseFloat(cols[3]) || 2.0; // Parses GPA or defaults to 2.0
                const enrolledUnits = parseInt(cols[4]) || 18; // Parses units or defaults to 18
                const status = cols[5].replace(/"/g, '').trim() || "Regular"; // Extracts status or defaults to Regular
                const term = cols[6] ? cols[6].replace(/"/g, '').trim() : "1st Semester S.Y. 2026-2027"; // Extracts term or assigns default

                if (!this.students.some(s => s.studentId === studentId)) { // Checks if student ID does not already exist
                    this.students.push({ studentId, name, course, gpa, enrolledUnits, status, term, grades: [] }); // Pushes new student record into array
                    addedCount++; // Increments successful import counter
                } // Ends inner if block
            } // Ends outer if block
        } // Ends loop
        this.saveToStorage(); // Saves updated student list to local storage
        this.logAction(`imported ${addedCount} student records via CSV`); // Logs the CSV import action
        return addedCount; // Returns total count of newly imported records
    } // Ends importCSV method
}; // Ends DataManager object declaration   