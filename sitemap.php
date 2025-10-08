<?php
include_once(__DIR__ . "/cors.php");
include_once(__DIR__ . "/conn.php");

// $baseUrl = "http://localhost/pbt-php-backend";
$baseUrl = "https://panavbiotech.com";


// Static URLs
$urls = [
    [ "loc" => "$baseUrl/", "priority" => "1.0" ],
    [ "loc" => "$baseUrl/products", "priority" => "0.9" ],
    [ "loc" => "$baseUrl/products?division=Orion", "priority" => "0.8" ],
    [ "loc" => "$baseUrl/products?division=Regal", "priority" => "0.8" ],
    [ "loc" => "$baseUrl/products?division=Iris", "priority" => "0.8" ],
    [ "loc" => "$baseUrl/our-company", "priority" => "0.9" ],
    [ "loc" => "$baseUrl/blogs", "priority" => "0.8" ],
    [ "loc" => "$baseUrl/events", "priority" => "0.8" ],
    [ "loc" => "$baseUrl/contact", "priority" => "0.8" ],
    [ "loc" => "$baseUrl/research-and-innovation", "priority" => "0.8" ],
    [ "loc" => "$baseUrl/cold-chain-management", "priority" => "0.8" ],
];

// ✅ Dynamic Products
$productQuery = $conn->query("SELECT slug, updated_at FROM products WHERE status = 'active'");
while ($row = $productQuery->fetch_assoc()) {
    $urls[] = [
        "loc" => "$baseUrl/product-details/" . htmlspecialchars($row['slug']),
        "priority" => "0.7",
        "lastmod" => date("Y-m-d", strtotime($row['updated_at']))
    ];
}

// ✅ Dynamic Blogs
$blogQuery = $conn->query("SELECT slug, updated_at FROM blogs WHERE status = 'active'");
while ($row = $blogQuery->fetch_assoc()) {
    $urls[] = [
        "loc" => "$baseUrl/blog-details/" . htmlspecialchars($row['slug']),
        "priority" => "0.6",
        "lastmod" => date("Y-m-d", strtotime($row['updated_at']))
    ];
}

// ✅ Generate XML
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($urls as $url) {
    echo "<url>";
    echo "<loc>{$url['loc']}</loc>";
    if (isset($url['lastmod'])) {
        echo "<lastmod>{$url['lastmod']}</lastmod>";
    }
    echo "<priority>{$url['priority']}</priority>";
    echo "</url>";
}

echo "</urlset>";
?>
