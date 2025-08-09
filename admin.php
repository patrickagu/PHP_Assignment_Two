<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/header.php';

// Restrict access to admins only
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = "Admin Dashboard - Grab & Go";
$error = '';
$success = '';

// Check if dashboard or CRUD interface
$showDashboard = true;
$crudTable = $_GET['table'] ?? '';
$action = $_GET['action'] ?? '';

// Define allowed tables for CRUD operations
$allowedTables = ['products', 'users_list', 'categories'];

// If a valid table is requested, show CRUD interface instead of dashboard
if (in_array($crudTable, $allowedTables)) {
    $showDashboard = false;
    $pageTitle = ucfirst($crudTable) . " Management - Grab & Go";

    // Handle different CRUD actions
    switch ($action) {
        case 'create':
            $pageTitle = "Create New - Grab & Go";

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Handle file upload if exists
                    $imagePath = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/uploads/' . $crudTable . '/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $fileName = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $destination = $uploadDir . $fileName;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $imagePath = 'uploads/' . $crudTable . '/' . $fileName;
                        }
                    }

                    // Prepare data
                    $columns = [];
                    $values = [];
                    $placeholders = [];

                    foreach ($_POST as $key => $value) {
                        if ($key !== 'table' && $key !== 'action') {
                            $columns[] = $key;
                            $values[] = is_array($value) ? json_encode($value) : $value;
                            $placeholders[] = '?';
                        }
                    }

                    // Add image path if uploaded
                    if ($imagePath) {
                        $columns[] = 'image_path';
                        $values[] = $imagePath;
                        $placeholders[] = '?';
                    }

                    // Build and execute query
                    $sql = "INSERT INTO $crudTable (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($values);

                    $success = "Record created successfully!";
                    $action = 'list'; // Redirect to list view
                } catch (PDOException $e) {
                    $error = "Error creating record: " . $e->getMessage();
                }
            }
            break;

        case 'edit':
            $pageTitle = "Edit Record - Grab & Go";
            $id = $_GET['id'] ?? 0;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Handle file upload if exists
                    $imagePath = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/uploads/' . $crudTable . '/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $fileName = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $destination = $uploadDir . $fileName;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $imagePath = 'uploads/' . $crudTable . '/' . $fileName;

                            // Delete old image if exists
                            $oldImage = $_POST['old_image'] ?? '';
                            if ($oldImage && file_exists(__DIR__ . '/' . $oldImage)) {
                                unlink(__DIR__ . '/' . $oldImage);
                            }
                        }
                    }

                    // Prepare data for update
                    $updates = [];
                    $values = [];

                    foreach ($_POST as $key => $value) {
                        if ($key !== 'table' && $key !== 'action' && $key !== 'id' && $key !== 'old_image') {
                            $updates[] = "$key = ?";
                            $values[] = is_array($value) ? json_encode($value) : $value;
                        }
                    }

                    // Add image path if uploaded
                    if ($imagePath) {
                        $updates[] = "image_path = ?";
                        $values[] = $imagePath;
                    }

                    // Add ID for WHERE clause
                    $values[] = $id;

                    // Build and execute query
                    $sql = "UPDATE $crudTable SET " . implode(', ', $updates) . " WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($values);

                    $success = "Record updated successfully!";
                    $action = 'list'; // Redirect to list view
                } catch (PDOException $e) {
                    $error = "Error updating record: " . $e->getMessage();
                }
            } else {
                // Fetch record to edit
                try {
                    $stmt = $pdo->prepare("SELECT * FROM $crudTable WHERE id = ?");
                    $stmt->execute([$id]);
                    $record = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$record) {
                        $error = "Record not found";
                        $action = 'list';
                    }
                } catch (PDOException $e) {
                    $error = "Error fetching record: " . $e->getMessage();
                    $action = 'list';
                }
            }
            break;

        case 'delete':
            $id = $_GET['id'] ?? 0;

            try {
                // First get image path if exists to delete the file
                $stmt = $pdo->prepare("SELECT image_path FROM $crudTable WHERE id = ?");
                $stmt->execute([$id]);
                $record = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($record && !empty($record['image_path']) && file_exists(__DIR__ . '/' . $record['image_path'])) {
                    unlink(__DIR__ . '/' . $record['image_path']);
                }

                // Delete the record
                $stmt = $pdo->prepare("DELETE FROM $crudTable WHERE id = ?");
                $stmt->execute([$id]);

                $success = "Record deleted successfully!";
            } catch (PDOException $e) {
                $error = "Error deleting record: " . $e->getMessage();
            }

            $action = 'list'; // Redirect to list view
            break;

        case 'list':
        default:
            // Fetch all records from the table
            try {
                $stmt = $pdo->query("SELECT * FROM $crudTable");
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "Error fetching records: " . $e->getMessage();
                $records = [];
            }
            break;
    }

    // Get table columns for forms
    if (in_array($action, ['create', 'edit']) && $crudTable) {
        try {
            $stmt = $pdo->query("DESCRIBE $crudTable");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $error = "Error fetching table structure: " . $e->getMessage();
            $columns = [];
        }
    }
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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

        /* Admin Dashboard Styles */
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        .admin-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }
        .admin-card:hover {
            transform: translateY(-5px);
        }
        .admin-card .card-body {
            display: flex;
            flex-direction: column;
        }
        .admin-card .btn {
            margin-top: auto;
        }

        /* CRUD specific styles */
        .crud-container {
            padding: 2rem 0;
        }
        .table-img {
            max-width: 100px;
            max-height: 100px;
        }
        .form-img-preview {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: #4CAF50;
            border: none;
        }
        .btn-primary:hover {
            background: #3d8b40;
        }
    </style>
</head>
<body>
<header class="main-header">
    <div class="header-content">
        <a href="index.php" class="logo-link">
            <img src="images/logo.png" alt="Grab & Go Logo" class="logo">
            <h1>Grab & Go</h1>
        </a>
        <nav class="main-nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="products.php" class="nav-link">Products</a>
            <a href="admin.php" class="nav-link active">Admin</a>
            <a href="profile.php" class="nav-link">Profile</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </nav>
    </div>
</header>

<div class="admin-container">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($showDashboard): ?>
        <!-- Dashboard View -->
        <h2>Admin Dashboard</h2>
        <div class="row mt-4 g-4">
            <div class="col-md-4">
                <div class="card admin-card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Products</h5>
                        <p class="card-text">Add, edit or remove products</p>
                        <a href="?table=products" class="btn btn-primary">Go to Products</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card admin-card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Users</h5>
                        <p class="card-text">View and manage user accounts</p>
                        <a href="?table=users_list" class="btn btn-primary">Go to Users</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card admin-card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Categories</h5>
                        <p class="card-text">Add or modify product categories</p>
                        <a href="?table=categories" class="btn btn-primary">Go to Categories</a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- CRUD Interface -->
        <div class="crud-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= ucfirst($crudTable) ?> Management</h2>
                <div>
                    <a href="admin.php" class="btn btn-outline-secondary me-2">Back to Dashboard</a>
                    <?php if ($action === 'list'): ?>
                        <a href="?table=<?= $crudTable ?>&action=create" class="btn btn-primary">Create New</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($action === 'list'): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                        <tr>
                            <?php if (!empty($records)): ?>
                                <?php foreach (array_keys($records[0]) as $column): ?>
                                    <?php if ($column !== 'password'): ?>
                                        <th><?= ucfirst(str_replace('_', ' ', $column)) ?></th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <th>Actions</th>
                            <?php else: ?>
                                <th>No records found</th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <?php foreach ($record as $key => $value): ?>
                                    <?php if ($key !== 'password'): ?>
                                        <td>
                                            <?php if ($key === 'image_path' && $value): ?>
                                                <img src="<?= htmlspecialchars($value) ?>" alt="Image" class="table-img">
                                            <?php else: ?>
                                                <?= htmlspecialchars(is_array(json_decode($value, true)) ? implode(', ', json_decode($value, true)) : $value) ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <td>
                                    <a href="?table=<?= $crudTable ?>&action=edit&id=<?= $record['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?table=<?= $crudTable ?>&action=delete&id=<?= $record['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (in_array($action, ['create', 'edit'])): ?>
                <form method="post" enctype="multipart/form-data" class="border p-4 rounded">
                    <input type="hidden" name="table" value="<?= $crudTable ?>">
                    <input type="hidden" name="action" value="<?= $action ?>">

                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $record['id'] ?>">
                    <?php endif; ?>

                    <?php foreach ($columns as $column): ?>
                        <?php if (!in_array($column, ['id', 'created_at', 'updated_at', 'password'])): ?>
                            <div class="mb-3">
                                <label for="<?= $column ?>" class="form-label"><?= ucfirst(str_replace('_', ' ', $column)) ?></label>

                                <?php if ($column === 'image_path'): ?>
                                    <?php if ($action === 'edit' && !empty($record[$column])): ?>
                                        <img src="<?= htmlspecialchars($record[$column]) ?>" alt="Current Image" class="form-img-preview">
                                        <input type="hidden" name="old_image" value="<?= htmlspecialchars($record[$column]) ?>">
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="<?= $column ?>" name="image" accept="image/*">
                                <?php elseif (strpos($column, 'description') !== false || strpos($column, 'content') !== false): ?>
                                    <textarea class="form-control" id="<?= $column ?>" name="<?= $column ?>"><?= $action === 'edit' ? htmlspecialchars($record[$column] ?? '') : '' ?></textarea>
                                <?php elseif (strpos($column, 'is_') === 0 || strpos($column, 'has_') === 0): ?>
                                    <select class="form-select" id="<?= $column ?>" name="<?= $column ?>">
                                        <option value="1" <?= ($action === 'edit' && ($record[$column] ?? false)) ? 'selected' : '' ?>>Yes</option>
                                        <option value="0" <?= ($action === 'edit' && !($record[$column] ?? false)) ? 'selected' : '' ?>>No</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control" id="<?= $column ?>" name="<?= $column ?>" value="<?= $action === 'edit' ? htmlspecialchars($record[$column] ?? '') : '' ?>">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary me-md-2"><?= $action === 'create' ? 'Create' : 'Update' ?></button>
                        <a href="?table=<?= $crudTable ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

