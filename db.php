<?php
session_start();

// Define site constants
define('SITE_NAME', 'My Blog CMS');
define('SITE_URL', 'http://localhost/blog-cms');

$host = "localhost";
$username = "root";
$password = "";
$database = "blog_cms";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SEO Helper Functions
function generateMetaTags($title = '', $description = '', $keywords = '', $image = '', $url = '', $type = 'article') {
    global $base_url;
    
    if (empty($url)) {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    
    if (empty($title)) {
        $title = SITE_NAME;
    }
    
    if (empty($description)) {
        $description = "Discover amazing blog posts on various topics. Read, learn, and get inspired.";
    }
    
    if (empty($image)) {
        $image = $base_url . '/uploads/default-thumbnail.jpg';
    }
    
    $meta_tags = '';
    $meta_tags .= '<title>' . htmlspecialchars($title) . '</title>' . PHP_EOL;
    $meta_tags .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
    $meta_tags .= '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">' . PHP_EOL;
    $meta_tags .= '<meta name="robots" content="index, follow">' . PHP_EOL;
    $meta_tags .= '<link rel="canonical" href="' . htmlspecialchars($url) . '">' . PHP_EOL;
    
    // Open Graph
    $meta_tags .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . PHP_EOL;
    $meta_tags .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
    $meta_tags .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . PHP_EOL;
    $meta_tags .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">' . PHP_EOL;
    $meta_tags .= '<meta property="og:type" content="' . htmlspecialchars($type) . '">' . PHP_EOL;
    $meta_tags .= '<meta property="og:site_name" content="' . htmlspecialchars(SITE_NAME) . '">' . PHP_EOL;
    
    // Twitter Card
    $meta_tags .= '<meta name="twitter:card" content="summary_large_image">' . PHP_EOL;
    $meta_tags .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . PHP_EOL;
    $meta_tags .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . PHP_EOL;
    $meta_tags .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . PHP_EOL;
    
    return $meta_tags;
}

function generateJSONLD($type = 'BlogPosting', $data = []) {
    $jsonld = [
        '@context' => 'https://schema.org',
        '@type' => $type,
        'publisher' => [
            '@type' => 'Organization',
            'name' => SITE_NAME,
            'url' => SITE_URL,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => SITE_URL . '/logo.png'
            ]
        ]
    ];
    
    if ($type === 'BlogPosting' && !empty($data)) {
        $jsonld['headline'] = $data['title'] ?? '';
        $jsonld['description'] = $data['description'] ?? '';
        $jsonld['image'] = $data['image'] ?? '';
        $jsonld['datePublished'] = $data['datePublished'] ?? '';
        $jsonld['dateModified'] = $data['dateModified'] ?? '';
        $jsonld['author'] = [
            '@type' => 'Person',
            'name' => $data['author'] ?? 'Admin'
        ];
    }
    
    return '<script type="application/ld+json">' . json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}
?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">