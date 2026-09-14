const DataManager = {
    students: [],

    async loadStudents() {
        try {
            const response = await fetch('api/students.php');
            const result = await response.json();
            if (result.status === 'success') {
                this.students = result.data;
            } else {
                throw new Error('API Error');
            }
        } catch (error) {
            console.warn('API error, loading fallback data.', error);
            this.students = [
                { studentId: "STU-102", name: "Jane Doe", course: "BS Information Technology", gpa: 3.20, enrolledUnits: 18, status: "Regular" },
                { studentId: "STU-103", name: "John Smith", course: "BS Computer Science", gpa: 2.40, enrolledUnits: 12, status: "Warning" },
                { studentId: "STU-104", name: "Maria Santos", course: "BS Business Administration", gpa: 3.95, enrolledUnits: 24, status: "Regular" },
                { studentId: "STU-105", name: "Alex Johnson", course: "BS Information Technology", gpa: 1.80, enrolledUnits: 9, status: "Probation" }
            ];
        }
        return this.students;
    },

    getStudents() { return this.students; },

    getStatistics() {
        const totalStudents = this.students.length;
        const totalUnits = this.students.reduce((acc, s) => acc + s.enrolledUnits, 0);
        const avgGPA = totalStudents > 0 
            ? (this.students.reduce((acc, s) => acc + s.gpa, 0) / totalStudents).toFixed(2) 
            : "0.00";
        const academicWarnings = this.students.filter(s => s.status === 'Warning' || s.status === 'Probation').length;

        return { totalStudents, totalUnits, avgGPA, academicWarnings };
    },

    filterStudents(search = "", courseFilter = "All", statusFilter = "All") {
        return this.students.filter(s => {
            const matchesSearch = s.name.toLowerCase().includes(search.toLowerCase()) || 
                                  s.studentId.toLowerCase().includes(search.toLowerCase());
            const matchesCourse = courseFilter === "All" || s.course === courseFilter;
            const matchesStatus = statusFilter === "All" || s.status === statusFilter;

            return matchesSearch && matchesCourse && matchesStatus;
        });
    },

    exportToCSV(data) {
        const headers = ["Student ID", "Name", "Course", "GPA", "Enrolled Units", "Academic Status"];
        const rows = data.map(s => [s.studentId, `"${s.name}"`, `"${s.course}"`, s.gpa.toFixed(2), s.enrolledUnits, s.status]);
        const csvContent = "data:text/csv;charset=utf-8," + [headers.join(","), ...rows.map(e => e.join(","))].join("\n");
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "student_academic_records.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};