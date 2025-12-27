<?php 
include 'db.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit();
}

// Get blog ID from URL
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin-blogs.php');
    exit();
}

$blog_id = intval($_GET['id']);

// Fetch blog data
$sql = "SELECT * FROM blogs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    header('Location: admin-blogs.php');
    exit();
}

$blog = $result->fetch_assoc();
$stmt->close();

// Fetch paragraphs
$para_sql = "SELECT * FROM blog_paragraphs WHERE blog_id = ? ORDER BY paragraph_order";
$para_stmt = $conn->prepare($para_sql);
$para_stmt->bind_param("i", $blog_id);
$para_stmt->execute();
$paragraphs = $para_stmt->get_result();

// Handle form submission
if(isset($_POST['submit'])){
    // Sanitize inputs
    $title = $conn->real_escape_string($_POST['title']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $meta_title = $conn->real_escape_string($_POST['meta_title']);
    $meta_desc = $conn->real_escape_string($_POST['meta_description']);
    $meta_keywords = $conn->real_escape_string($_POST['meta_keywords']);
    $short_desc = $conn->real_escape_string($_POST['short_description']);

    // Handle file upload
    $thumbnail = $blog['thumbnail']; // Keep existing if no new upload
    if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK){
        $uploadDir = "uploads/";
        $fileName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
        $targetFile = $uploadDir . $fileName;
        
        $check = getimagesize($_FILES['thumbnail']['tmp_name']);
        if($check !== false){
            if(move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)){
                // Delete old thumbnail if exists
                if($blog['thumbnail'] && file_exists($uploadDir . $blog['thumbnail'])){
                    unlink($uploadDir . $blog['thumbnail']);
                }
                $thumbnail = $fileName;
            }
        }
    }

    // Update blog
    $update_sql = "UPDATE blogs SET 
                    title = ?, 
                    slug = ?, 
                    thumbnail = ?, 
                    short_description = ?, 
                    meta_title = ?, 
                    meta_description = ?, 
                    meta_keywords = ?,
                    updated_at = NOW()
                   WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssssi", $title, $slug, $thumbnail, $short_desc, $meta_title, $meta_desc, $meta_keywords, $blog_id);
    
    if($update_stmt->execute()){
        // Delete existing paragraphs
        $delete_sql = "DELETE FROM blog_paragraphs WHERE blog_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $blog_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Insert updated paragraphs
        if(isset($_POST['para_title'])){
            $insert_sql = "INSERT INTO blog_paragraphs (blog_id, paragraph_title, paragraph_content, paragraph_order) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            
            foreach($_POST['para_title'] as $i => $para_title){
                $para_content = $conn->real_escape_string($_POST['para_content'][$i]);
                $order = $i + 1;
                $insert_stmt->bind_param("issi", $blog_id, $para_title, $para_content, $order);
                $insert_stmt->execute();
            }
            $insert_stmt->close();
        }
        
        $success_message = "Blog updated successfully!";
        $blog['thumbnail'] = $thumbnail; // Update local variable for preview
    } else {
        $error_message = "Error updating blog: " . $update_stmt->error;
    }
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog - Admin Dashboard</title>
    
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
        
        .current-image {
            max-width: 200px;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border: 2px solid var(--gray-300);
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: var(--border-radius);
            margin-top: 1rem;
            border: 2px solid var(--gray-300);
            display: none;
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
            <h2>Edit Blog Post</h2>
            <p>Make changes to your blog post below</p>
        </div>

        <?php
        if(isset($success_message)){
            echo '<div class="alert alert-success fade-in">
                    <i class="fas fa-check-circle"></i>
                    ' . $success_message . ' <a href="blog-detail.php?slug=' . $slug . '" target="_blank">View Blog</a>
                  </div>';
        }
        
        if(isset($error_message)){
            echo '<div class="alert alert-error fade-in">
                    <i class="fas fa-exclamation-circle"></i>
                    ' . $error_message . '
                  </div>';
        }
        ?>

        <form method="POST" enctype="multipart/form-data" class="blog-form fade-in">
            <!-- Basic Information -->
            <div class="card mb-4">
                <h3 class="mb-3"><i class="fas fa-info-circle mr-2"></i>Basic Information</h3>
                <div class="form-group">
                    <label for="title">Blog Title *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($blog['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description *</label>
                    <textarea id="short_description" name="short_description" class="form-control" 
                              required><?php echo htmlspecialchars($blog['short_description']); ?></textarea>
                </div>
            </div>

            <!-- SEO Information -->
            <div class="card mb-4">
                <h3 class="mb-3"><i class="fas fa-search mr-2"></i>SEO Optimization</h3>
                <div class="form-group">
                    <label for="meta_title">Meta Title *</label>
                    <input type="text" id="meta_title" name="meta_title" class="form-control" 
                           value="<?php echo htmlspecialchars($blog['meta_title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="meta_description">Meta Description *</label>
                    <textarea id="meta_description" name="meta_description" class="form-control" 
                              rows="3" required><?php echo htmlspecialchars($blog['meta_description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="meta_keywords">Meta Keywords *</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" 
                           value="<?php echo htmlspecialchars($blog['meta_keywords']); ?>" required>
                </div>
            </div>

            <!-- Thumbnail Upload -->
            <div class="card mb-4">
                <h3 class="mb-3"><i class="fas fa-image mr-2"></i>Featured Image</h3>
                <div class="form-group">
                    <?php if($blog['thumbnail']): ?>
                        <p class="mb-2">Current Image:</p>
                        <img src="uploads/<?php echo htmlspecialchars($blog['thumbnail']); ?>" 
                             alt="Current thumbnail" class="current-image">
                    <?php endif; ?>
                    
                    <label for="thumbnail" class="mb-2">Upload New Image (Leave empty to keep current):</label>
                    <div class="file-upload">
                        <input type="file" id="thumbnail" name="thumbnail" accept="image/*" onchange="previewImage(this)">
                        <label for="thumbnail" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt fa-2x"></i>
                            <div>
                                <strong>Click to upload new thumbnail</strong>
                                <p class="mb-0">PNG, JPG, GIF up to 5MB</p>
                            </div>
                        </label>
                    </div>
                    <img id="imagePreview" class="preview-image" alt="Image preview">
                </div>
            </div>

            <!-- Blog Content -->
            <div class="card">
                <div class="paragraphs-container">
                    <div class="flex justify-between items-center mb-3">
                        <h3><i class="fas fa-paragraph mr-2"></i>Blog Content</h3>
                        <button type="button" onclick="addParagraph()" class="btn btn-outline btn-sm">
                            <i class="fas fa-plus"></i> Add Paragraph
                        </button>
                    </div>
                    
                    <div id="paragraphs">
                        <?php
                        if($paragraphs->num_rows > 0){
                            $counter = 1;
                            while($para = $paragraphs->fetch_assoc()){
                                ?>
                                <div class="para-block">
                                    <div class="para-header">
                                        <h4>Paragraph <?php echo $counter; ?></h4>
                                        <button type="button" onclick="removeParagraph(this)" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="para_title[]" class="form-control" 
                                               value="<?php echo htmlspecialchars($para['paragraph_title']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="para_content[]" class="form-control" rows="4" required><?php echo htmlspecialchars($para['paragraph_content']); ?></textarea>
                                    </div>
                                </div>
                                <?php
                                $counter++;
                            }
                        } else {
                            ?>
                            <div class="para-block">
                                <div class="para-header">
                                    <h4>Paragraph 1</h4>
                                    <button type="button" onclick="removeParagraph(this)" class="btn btn-danger btn-sm" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="para_title[]" class="form-control" placeholder="Paragraph Title (e.g., Introduction)" required>
                                </div>
                                <div class="form-group">
                                    <textarea name="para_content[]" class="form-control" placeholder="Write your paragraph content here..." rows="4" required></textarea>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Blog
                </button>
                <a href="admin-blogs.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <a href="blog-detail.php?slug=<?php echo $blog['slug']; ?>" target="_blank" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Preview
                </a>
                <a href="admin-delete-blog.php?id=<?php echo $blog['id']; ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to delete this blog? This action cannot be undone.');">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
        </form>
    </div>

    <script>
    let paraCount = <?php echo $paragraphs->num_rows > 0 ? $paragraphs->num_rows : 1; ?>;
    
    function addParagraph() {
        paraCount++;
        let div = document.createElement("div");
        div.classList.add("para-block", "slide-in");
        div.innerHTML = `
            <div class="para-header">
                <h4>Paragraph ${paraCount}</h4>
                <button type="button" onclick="removeParagraph(this)" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="form-group">
                <input type="text" name="para_title[]" class="form-control" placeholder="Paragraph Title (e.g., Introduction)" required>
            </div>
            <div class="form-group">
                <textarea name="para_content[]" class="form-control" placeholder="Write your paragraph content here..." rows="4" required></textarea>
            </div>
        `;
        document.getElementById("paragraphs").appendChild(div);
    }
    
    function removeParagraph(button) {
        if(document.querySelectorAll('.para-block').length > 1) {
            button.closest('.para-block').remove();
            updateParaNumbers();
        }
    }
    
    function updateParaNumbers() {
        const paragraphs = document.querySelectorAll('.para-block');
        paragraphs.forEach((para, index) => {
            para.querySelector('h4').textContent = `Paragraph ${index + 1}`;
        });
        paraCount = paragraphs.length;
    }
    
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
            preview.src = '';
        }
    }
    
    // Show delete button on first paragraph if multiple paragraphs exist
    if(document.querySelectorAll('.para-block').length > 1){
        document.querySelectorAll('.para-block .btn-danger').forEach(btn => btn.style.display = 'block');
    }
    </script>
</body>
</html>
<?php
$para_stmt->close();
$conn->close();
?>