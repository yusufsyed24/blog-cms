<?php 
include 'db.php';

$slug = $_GET['slug'];
$stmt = $conn->prepare("SELECT * FROM blogs WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if(!$blog){
    header('Location: index.php');
    exit();
}

// Fetch paragraphs
$para_stmt = $conn->prepare("SELECT * FROM blog_paragraphs WHERE blog_id = ? ORDER BY paragraph_order");
$para_stmt->bind_param("i", $blog['id']);
$para_stmt->execute();
$paragraphs = $para_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['meta_title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($blog['meta_description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($blog['meta_keywords']); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($blog['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($blog['short_description']); ?>">
    <meta property="og:image" content="uploads/<?php echo htmlspecialchars($blog['thumbnail']); ?>">
    <meta property="og:url" content="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:type" content="article">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($blog['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($blog['short_description']); ?>">
    <meta name="twitter:image" content="uploads/<?php echo htmlspecialchars($blog['thumbnail']); ?>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    
    <style>
        .blog-header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('uploads/<?php echo htmlspecialchars($blog['thumbnail']); ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 20px;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .blog-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .blog-header .meta {
            font-size: 1.1rem;
            opacity: 0.9;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .blog-content {
            max-width: 900px;
            margin: 0 auto 50px;
        }
        
        .featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: var(--border-radius-xl);
            margin-bottom: 40px;
            box-shadow: var(--shadow-lg);
        }
        
        .article-body {
            font-size: 1.125rem;
            line-height: 1.8;
            color: var(--gray-700);
        }
        
        .article-body h3 {
            color: var(--dark-color);
            margin-top: 40px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .share-buttons {
            background: var(--gray-100);
            border-radius: var(--border-radius);
            padding: 20px;
            margin: 40px 0;
            text-align: center;
        }
        
        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        
        .share-facebook { background: #3b5998; color: white; }
        .share-twitter { background: #1da1f2; color: white; }
        .share-linkedin { background: #0077b5; color: white; }
        .share-whatsapp { background: #25d366; color: white; }
        
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .back-to-blogs {
            text-align: center;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid var(--gray-300);
        }
        
        @media (max-width: 768px) {
            .blog-header {
                padding: 50px 20px;
            }
            
            .blog-header h1 {
                font-size: 2rem;
            }
            
            .article-body {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="fas fa-blog me-2"></i>BlogHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#blogs">Blogs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Blog Header -->
    <header class="blog-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
            <div class="meta">
                <span><i class="far fa-calendar me-2"></i><?php echo date('F d, Y', strtotime($blog['created_at'])); ?></span>
                <span><i class="far fa-clock me-2"></i><?php echo ceil(str_word_count($blog['short_description']) / 200); ?> min read</span>
            </div>
        </div>
    </header>

    <!-- Blog Content -->
    <div class="container">
        <article class="blog-content">
            <!-- Featured Image -->
            <img src="uploads/<?php echo htmlspecialchars($blog['thumbnail']); ?>" 
                 alt="<?php echo htmlspecialchars($blog['title']); ?>" 
                 class="featured-image">
            
            <!-- Short Description -->
            <div class="lead mb-5 fs-4" style="color: var(--gray-700);">
                <?php echo nl2br(htmlspecialchars($blog['short_description'])); ?>
            </div>
            
            <!-- Blog Content -->
            <div class="article-body">
                <?php
                if($paragraphs->num_rows > 0){
                    while($para = $paragraphs->fetch_assoc()){
                        ?>
                        <div class="mb-5">
                            <h3 class="border-bottom pb-2"><?php echo htmlspecialchars($para['paragraph_title']); ?></h3>
                            <div class="paragraph-content">
                                <?php echo nl2br(htmlspecialchars($para['paragraph_content'])); ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            
            <!-- Share Buttons -->
            <div class="share-buttons">
                <h4 class="mb-3">Share this article:</h4>
                <?php
                $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $share_title = urlencode(htmlspecialchars($blog['title']));
                ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" 
                   target="_blank" 
                   class="share-btn share-facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($current_url); ?>&text=<?php echo $share_title; ?>" 
                   target="_blank" 
                   class="share-btn share-twitter">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($current_url); ?>&title=<?php echo $share_title; ?>" 
                   target="_blank" 
                   class="share-btn share-linkedin">
                    <i class="fab fa-linkedin-in"></i> LinkedIn
                </a>
                <a href="https://wa.me/?text=<?php echo $share_title . ' ' . urlencode($current_url); ?>" 
                   target="_blank" 
                   class="share-btn share-whatsapp">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            </div>
            
            <!-- Back to Blogs -->
            <div class="back-to-blogs">
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i> Back to All Blogs
                </a>
            </div>
        </article>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> BlogCMS. All rights reserved.</p>
            <p class="mb-0">
                <a href="admin-login.php" class="text-white-50">Admin Login</a> | 
                <a href="index.php" class="text-white-50">Home</a>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add smooth scrolling to anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$para_stmt->close();
$conn->close();
?>