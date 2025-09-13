<?php
include_once(__DIR__ . "/../cors.php");
include_once(__DIR__ . "/../conn.php");

$blogId = isset($_GET['blogId']) && $_GET['blogId'] !== '' ? $conn->real_escape_string($_GET['blogId']) : null;

if (!$blogId) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing blogId"
    ]);
    exit;
}

// Get the current blog (full row)
$currentQuery = "SELECT * FROM blogs WHERE id = '$blogId' AND status = 'active' LIMIT 1";
$currentResult = $conn->query($currentQuery);

if (!$currentResult || $currentResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Blog not found"
    ]);
    exit;
}

$currentBlog = $currentResult->fetch_assoc();
$currentDate = $currentBlog['created_at'];

// Get 1 previous blog
$prevQuery = "
    SELECT * FROM blogs 
    WHERE status = 'active' AND created_at < '$currentDate'
    ORDER BY created_at DESC 
    LIMIT 1
";

// Get 2 next blogs
$nextQuery = "
    SELECT * FROM blogs 
    WHERE status = 'active' AND created_at > '$currentDate'
    ORDER BY created_at ASC 
    LIMIT 2
";

$previous = [];
$next = [];

// Fetch previous
if ($prevRes = $conn->query($prevQuery)) {
    while ($row = $prevRes->fetch_assoc()) {
        $previous[] = $row;
    }
}

// Fetch next
if ($nextRes = $conn->query($nextQuery)) {
    while ($row = $nextRes->fetch_assoc()) {
        $next[] = $row;
    }
}

// Final blogs array (max 3 items)
$blogs = [];

if (count($previous) > 0 && count($next) > 0) {
    // Case 1: Both exist → 1 prev + current + 1 next
    $blogs = array_merge($previous, [$currentBlog], [$next[0]]);

} elseif (count($previous) > 0 && count($next) === 0) {
    // Case 2: Only prev → last 2 prev + current
    $prev2Query = "
        SELECT * FROM blogs 
        WHERE status = 'active' AND created_at < '$currentDate'
        ORDER BY created_at DESC 
        LIMIT 2
    ";
    $prev2 = [];
    if ($res = $conn->query($prev2Query)) {
        while ($row = $res->fetch_assoc()) {
            $prev2[] = $row;
        }
    }
    $blogs = array_reverse($prev2); // ensure chronological order
    $blogs[] = $currentBlog;

} elseif (count($previous) === 0 && count($next) > 0) {
    // Case 3: Only next → current + next 2
    $next2Query = "
        SELECT * FROM blogs 
        WHERE status = 'active' AND created_at > '$currentDate'
        ORDER BY created_at ASC 
        LIMIT 2
    ";
    $next2 = [];
    if ($res = $conn->query($next2Query)) {
        while ($row = $res->fetch_assoc()) {
            $next2[] = $row;
        }
    }
    $blogs = array_merge([$currentBlog], $next2);

} else {
    // Case 4: No prev & no next → only current
    $blogs = [$currentBlog];
}

// Return response
echo json_encode([
    "status" => "success",
    "count" => count($blogs),
    "data" => $blogs
]);
?>
