<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$students = [
    ["studentId" => "STU-102", "name" => "Jane Doe", "course" => "BS Information Technology", "gpa" => 3.20, "enrolledUnits" => 18, "status" => "Regular"],
    ["studentId" => "STU-103", "name" => "John Smith", "course" => "BS Computer Science", "gpa" => 2.40, "enrolledUnits" => 12, "status" => "Warning"],
    ["studentId" => "STU-104", "name" => "Maria Santos", "course" => "BS Business Administration", "gpa" => 3.95, "enrolledUnits" => 24, "status" => "Regular"],
    ["studentId" => "STU-105", "name" => "Alex Johnson", "course" => "BS Information Technology", "gpa" => 1.80, "enrolledUnits" => 9, "status" => "Probation"]
];

echo json_encode(["status" => "success", "data" => $students]);
exit();
?>