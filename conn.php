<?php
$host = "localhost";   // same as your Node config
$user = "root";        // database username upskimmy_pbt
$pass = "";            // database password  PBTdata!@#123
$dbname = "pbt";       // database name  upskimmy_pbt

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

// If needed, you can echo success in CLI testing
// echo "Connected to MySQL database ✅";
?>

