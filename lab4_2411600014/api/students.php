<?php // Opens the PHP script tag
header('Content-Type: application/json'); // Sets the response header to tell the browser that the output format is JSON
header('Access-Control-Allow-Origin: *'); // Allows any domain/origin to access this API (CORS policy enabled)

$students = [ // Initializes an array variable named $students to hold multiple student records
    ["studentId" => "STU-102", "name" => "Jane Doe", "course" => "BS Information Technology", "gpa" => 3.20, "enrolledUnits" => 18, "status" => "Regular"], // Stores data for the first student
    ["studentId" => "STU-103", "name" => "John Smith", "course" => "BS Computer Science", "gpa" => 2.40, "enrolledUnits" => 12, "status" => "Warning"], // Stores data for the second student
    ["studentId" => "STU-104", "name" => "Maria Santos", "course" => "BS Business Administration", "gpa" => 3.95, "enrolledUnits" => 24, "status" => "Regular"], // Stores data for the third student
    ["studentId" => "STU-105", "name" => "Alex Johnson", "course" => "BS Information Technology", "gpa" => 1.80, "enrolledUnits" => 9, "status" => "Probation"] // Stores data for the fourth student
]; // Ends and closes the $students array declaration

echo json_encode(["status" => "success", "data" => $students]); // Converts the PHP array into a JSON string and prints/outputs it
exit(); // Stops the execution of the script immediately
?> // Closes the PHP script tag