<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/header.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = "Upload Item - Grab & Go";
$error = '';
$success = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['item_photo']) && $_FILES['item_photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['item_photo']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = __DIR__ . '/uploads/requested_items/';

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $error = "Failed to create upload directory";
                    error_log("Failed to create directory: " . $uploadDir);
                }
            }

            // Verify directory is writable
            if (!is_writable($uploadDir)) {
                $error = "Upload directory is not writable";
                error_log("Directory not writable: " . $uploadDir);
            } else {
                $fileName = uniqid() . '_' . basename($_FILES['item_photo']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['item_photo']['tmp_name'], $targetPath)) {
                    // Rest of your success code
                } else {
                    $error = "Upload failed. Error: " . $_FILES['item_photo']['error'];
                    error_log("Upload failed. PHP Error: " . $_FILES['item_photo']['error']);
                    error_log("Trying to move to: " . $targetPath);
                }
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    } else {
        $error = "File upload error: " . getUploadError($_FILES['item_photo']['error']);
    }
}

function getUploadError($code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
    ];
    return $errors[$code] ?? 'Unknown upload error';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Header Styles */
        .main-header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
        }
        .logo {
            height: 50px;
            margin-right: 15px;
        }
        .main-nav {
            display: flex;
            gap: 20px;
        }
        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 5px 0;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: #4CAF50;
            border-bottom-color: #4CAF50;
        }

        /* Form Styles */
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 2rem auto;
            border: none;
            border-radius: 8px;
            max-width: 600px;
        }
        .card-header {
            background: #4CAF50;
            color: white;
            border-radius: 8px 8px 0 0 !important;
            padding: 1.5rem;
        }
        .card-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        .card-body {
            padding: 2rem;
        }
        .btn-primary {
            background: #4CAF50;
            border: none;
        }
        .btn-primary:hover {
            background: #3d8b40;
        }
        .btn-outline-secondary {
            border-color: #4CAF50;
            color: #4CAF50;
        }
        .btn-outline-secondary:hover {
            background: #4CAF50;
            color: white;
        }
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            margin-top: 15px;
            display: none;
        }
    </style>
</head>
<body>
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo-link">
                <img src="images/logo.png" alt="Grab & Go Logo" class="logo">
                <h1>Grab & Go</h1>
            </a>
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Home</a>
                <a href="products.php" class="nav-link">Products</a>
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="upload_item.php" class="nav-link active">Request Item</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Request an Item</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="item_photo" class="form-label">Upload Photo of Item</label>
                            <input type="file" class="form-control" id="item_photo" name="item_photo" accept="image/*" required>
                            <div class="form-text">Upload a clear photo of the item you'd like us to stock</div>
                            <img id="preview" class="preview-image" alt="Image preview">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Item Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brand, size, color, or any other details"></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php" class="btn btn-outline-secondary me-md-2 px-4">Back to Home</a>
                            <button type="submit" class="btn btn-primary px-4">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Image preview
    document.getElementById('item_photo').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
</script>
</body>
</html>
