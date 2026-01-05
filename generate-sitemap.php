<?php
// Generate sitemap.xml file
ob_start();
include 'sitemap.php';
$sitemap_content = ob_get_clean();

// Save to file
file_put_contents('sitemap.xml', $sitemap_content);

echo "Sitemap generated successfully!<br>";
echo '<a href="sitemap.xml" target="_blank">View Sitemap</a>';
?>