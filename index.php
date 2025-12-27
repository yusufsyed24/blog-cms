<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogHub - Read Amazing Articles</title>
    <meta name="description" content="Discover amazing blog posts on various topics. Read, learn, and get inspired.">
    <meta name="keywords" content="blog, articles, reading, content, news">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .blog-card {
            background: white;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }
        
        .blog-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .blog-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .blog-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--gray-900);
        }
        
        .blog-excerpt {
            color: var(--gray-600);
            margin-bottom: 20px;
            flex-grow: 1;
        }
        
        .blog-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
            color: var(--gray-500);
            font-size: 0.9rem;
        }
        
        .read-more {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .read-more:hover {
            color: var(--secondary-color);
            gap: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--gray-500);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .blog-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#blogs">Blogs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Discover Amazing Stories</h1>
            <p>Explore our collection of insightful blog posts written by passionate writers. Learn something new every day.</p>
            <a href="#blogs" class="btn btn-light btn-lg">
                <i class="fas fa-arrow-down me-2"></i> Explore Blogs
            </a>
        </div>
    </section>

    <!-- Blogs Section -->
    <section id="blogs" class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Latest Articles</h2>
            <p class="lead text-muted">Stay updated with our newest content</p>
        </div>

        <div class="blog-grid">
            <?php
            $result = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
            
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    ?>
                    <article class="blog-card">
                        <img src="uploads/<?php echo htmlspecialchars($row['thumbnail']); ?>" 
                             alt="<?php echo htmlspecialchars($row['title']); ?>" 
                             class="blog-image">
                        <div class="blog-content">
                            <h3 class="blog-title">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                            <p class="blog-excerpt">
                                <?php echo htmlspecialchars(substr($row['short_description'], 0, 150)); ?>...
                            </p>
                            <div class="blog-meta">
                                <span>
                                    <i class="far fa-calendar me-1"></i>
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </span>
                                <a href="blog-detail.php?slug=<?php echo htmlspecialchars($row['slug']); ?>" 
                                   class="read-more">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php
                }
            } else {
                ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3 class="mb-3">No Blogs Yet</h3>
                    <p class="mb-0">Check back soon for amazing content!</p>
                </div>
                <?php
            }
            ?>
        </div>
        
        <?php if($result->num_rows > 0): ?>
            <div class="text-center mt-5">
                <p class="text-muted">Showing <?php echo $result->num_rows; ?> blog posts</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h3 class="mb-3">BlogHub</h3>
                    <p class="text-white-50">Sharing knowledge and inspiring readers through quality content.</p>
                </div>
                <div class="col-lg-3">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50 text-decoration-none">Home</a></li>
                        <li><a href="#blogs" class="text-white-50 text-decoration-none">Blogs</a></li>
                        <li><a href="admin-login.php" class="text-white-50 text-decoration-none">Admin</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5 class="mb-3">Connect</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white-50"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white-50"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white-50"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white-50"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-white-50 my-4">
            <div class="text-center">
                <p class="mb-0 text-white-50">&copy; <?php echo date('Y'); ?> BlogHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add animation to blog cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Apply to all blog cards
        document.querySelectorAll('.blog-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>