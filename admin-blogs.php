<?php 
include 'db.php';

// Check if user is logged in
// session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit();
}

// Fetch all blogs
$sql = "SELECT * FROM blogs ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blogs - Admin Dashboard</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    
    <style>
        .admin-nav {
            background: white;
            box-shadow: var(--shadow-md);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-link {
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .blog-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="admin-nav">
        <div class="nav-container">
            <a href="admin-blogs.php" class="nav-logo">
                <i class="fas fa-blog"></i> BlogCMS
            </a>
            <div class="nav-links">
                <a href="admin-blogs.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="admin-add-blog.php" class="nav-link">
                    <i class="fas fa-plus-circle"></i> Add Blog
                </a>
                <a href="admin-blogs.php" class="nav-link active">
                    <i class="fas fa-list"></i> Manage Blogs
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-header">
            <div class="flex justify-between items-center">
                <div>
                    <h2>Manage Blog Posts</h2>
                    <p>View, edit, and delete your blog posts</p>
                </div>
                <a href="admin-add-blog.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Blog
                </a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid mb-4">
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-card-content">
                    <h3><?php echo $result->num_rows; ?></h3>
                    <p>Total Posts</p>
                </div>
            </div>
            
            <?php
            // Get today's posts
            $today_sql = "SELECT COUNT(*) as count FROM blogs WHERE DATE(created_at) = CURDATE()";
            $today_result = $conn->query($today_sql);
            $today = $today_result->fetch_assoc();
            ?>
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-card-content">
                    <h3><?php echo $today['count']; ?></h3>
                    <p>Published Today</p>
                </div>
            </div>
        </div>

        <!-- Blog Posts Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Short Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php if($row['thumbnail']): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($row['thumbnail']); ?>" 
                                             alt="Thumbnail" class="blog-image">
                                    <?php else: ?>
                                        <span class="text-muted">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                    <small class="text-muted">Slug: <?php echo $row['slug']; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars(substr($row['short_description'], 0, 100)) . '...'; ?></td>
                                <td>
                                    <?php 
                                    $date = new DateTime($row['created_at']);
                                    echo $date->format('M d, Y');
                                    ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="admin-edit-blog.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="blog-detail.php?slug=<?php echo $row['slug']; ?>" 
                                           target="_blank" 
                                           class="btn btn-outline btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="admin-delete-blog.php?id=<?php echo $row['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this blog?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No blog posts found. <a href="admin-add-blog.php">Create your first blog</a></p>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>