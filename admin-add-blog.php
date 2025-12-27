<?php 
include 'db.php';

// Check if user is logged in (you should implement proper authentication)
// session_start();
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: admin-login.php');
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Blog - Admin blogs</title>
    
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
                <a href="admin-add-blog.php" class="nav-link active">
                    <i class="fas fa-plus-circle"></i> Add Blog
                </a>
                <a href="admin-blogs.php" class="nav-link">
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
            <h2>Create New Blog Post</h2>
            <p>Fill in the details below to publish a new blog post</p>
        </div>

        <?php
        if(isset($_POST['submit'])){
            // Sanitize inputs
            $title = $conn->real_escape_string($_POST['title']);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            $meta_title = $conn->real_escape_string($_POST['meta_title']);
            $meta_desc = $conn->real_escape_string($_POST['meta_description']);
            $meta_keywords = $conn->real_escape_string($_POST['meta_keywords']);
            $short_desc = $conn->real_escape_string($_POST['short_description']);

            // Handle file upload
            $uploadDir = "uploads/";
            $thumbnail = "";
            if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK){
                $fileName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
                $targetFile = $uploadDir . $fileName;
                
                // Check if image file is actual image
                $check = getimagesize($_FILES['thumbnail']['tmp_name']);
                if($check !== false){
                    if(move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)){
                        $thumbnail = $fileName;
                    }
                }
            }

            // Insert blog
            $sql = "INSERT INTO blogs 
                    (title, slug, thumbnail, short_description, meta_title, meta_description, meta_keywords, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $title, $slug, $thumbnail, $short_desc, $meta_title, $meta_desc, $meta_keywords);
            
            if($stmt->execute()){
                $blog_id = $stmt->insert_id;
                
                // Insert paragraphs
                if(isset($_POST['para_title'])){
                    $para_sql = "INSERT INTO blog_paragraphs (blog_id, paragraph_title, paragraph_content, paragraph_order) VALUES (?, ?, ?, ?)";
                    $para_stmt = $conn->prepare($para_sql);
                    
                    foreach($_POST['para_title'] as $i => $para_title){
                        $para_content = $conn->real_escape_string($_POST['para_content'][$i]);
                        $order = $i + 1;
                        $para_stmt->bind_param("issi", $blog_id, $para_title, $para_content, $order);
                        $para_stmt->execute();
                    }
                    $para_stmt->close();
                }
                
                echo '<div class="alert alert-success fade-in">
                        <i class="fas fa-check-circle"></i>
                        Blog published successfully! <a href="blog-detail.php?slug=' . $slug . '" target="_blank">View Blog</a>
                    </div>';
            } else {
                echo '<div class="alert alert-error fade-in">
                        <i class="fas fa-exclamation-circle"></i>
                        Error: ' . $stmt->error . '
                    </div>';
            }
            $stmt->close();
        }
        ?>

        <form method="POST" enctype="multipart/form-data" class="blog-form fade-in">
            <!-- Basic Information -->
            <div class="card mb-4">
                <h3 class="mb-3"><i class="fas fa-info-circle mr-2"></i>Basic Information</h3>
                <div class="form-group">
                    <label for="title">Blog Title *</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Enter blog title" required>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description *</label>
                    <textarea id="short_description" name="short_description" class="form-control" placeholder="Brief description of the blog" required></textarea>
                </div>
            </div>

            <!-- SEO Information -->
            <div class="card mb-4">
                <h3 class="mb-3"><i class="fas fa-search mr-2"></i>SEO Optimization</h3>
                <div class="form-group">
                    <label for="meta_title">Meta Title *</label>
                    <input type="text" id="meta_title" name="meta_title" class="form-control" placeholder="Title for search engines" required>
                </div>
                
                <div class="form-group">
                    <label for="meta_description">Meta Description *</label>
                    <textarea id="meta_description" name="meta_description" class="form-control" placeholder="Description for search results" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="meta_keywords">Meta Keywords *</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" placeholder="Keywords separated by commas" required>
                </div>
            </div>

            <!-- Thumbnail Upload -->
            <div class="card mb-4">
                <h3 class="mb-3"><i class="fas fa-image mr-2"></i>Featured Image</h3>
                <div class="form-group">
                    <div class="file-upload">
                        <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required onchange="previewImage(this)">
                        <label for="thumbnail" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt fa-2x"></i>
                            <div>
                                <strong>Click to upload thumbnail</strong>
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
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Publish Blog
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset Form
                </button>
                <a href="admin-blogs.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
    let paraCount = 1;
    
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
    
    // Show delete button on first paragraph after adding more
    document.querySelector('#paragraphs .para-block:first-child .btn-danger').style.display = 'block';
    </script>
</body>
</html>