<?php

$corsHeaders = [
    "Access-Control-Allow-Origin: *",
    "Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS",
    "Access-Control-Allow-Headers: Content-Type, Authorization",
    "Content-Type: application/json"
];

// Send headers
foreach ($corsHeaders as $header) {
    header($header);
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
