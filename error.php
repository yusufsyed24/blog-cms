<?php
$error_code = $_GET['code'] ?? '404';
$error_messages = [
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '403' => 'Forbidden',
    '404' => 'Page Not Found',
    '500' => 'Internal Server Error'
];
$error_title = $error_messages[$error_code] ?? 'Error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $error_code; ?> - <?php echo $error_title; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--gray-700);
        }
        .error-actions {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="error-container">
        <div>
            <div class="error-code"><?php echo $error_code; ?></div>
            <h1 class="error-message"><?php echo $error_title; ?></h1>
            <p class="lead mb-4">
                <?php
                switch($error_code) {
                    case '404':
                        echo "The page you're looking for doesn't exist or has been moved.";
                        break;
                    case '403':
                        echo "You don't have permission to access this page.";
                        break;
                    case '500':
                        echo "Something went wrong on our servers. We're working to fix it.";
                        break;
                    default:
                        echo "An error occurred. Please try again later.";
                }
                ?>
            </p>
            <div class="error-actions">
                <a href="index.php" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-home"></i> Go Home
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
            </div>
            <div class="mt-4">
                <small class="text-muted">
                    If you believe this is an error, please <a href="contact.php">contact us</a>.
                </small>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
