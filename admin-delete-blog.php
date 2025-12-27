<?php
include 'db.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit();
}

if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $blog_id = intval($_GET['id']);
    
    // Get blog details to delete thumbnail
    $sql = "SELECT thumbnail FROM blogs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();
    $stmt->close();
    
    // Delete thumbnail file
    if($blog['thumbnail'] && file_exists("uploads/" . $blog['thumbnail'])){
        unlink("uploads/" . $blog['thumbnail']);
    }
    
    // Delete paragraphs
    $delete_para_sql = "DELETE FROM blog_paragraphs WHERE blog_id = ?";
    $delete_para_stmt = $conn->prepare($delete_para_sql);
    $delete_para_stmt->bind_param("i", $blog_id);
    $delete_para_stmt->execute();
    $delete_para_stmt->close();
    
    // Delete blog
    $delete_sql = "DELETE FROM blogs WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $blog_id);
    
    if($delete_stmt->execute()){
        $_SESSION['success'] = "Blog deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting blog: " . $delete_stmt->error;
    }
    $delete_stmt->close();
}

header('Location: admin-blogs.php');
exit();
?>