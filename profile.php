<?php
session_start();
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/header.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = "Profile - Grab & Go";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile information update
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email)) {
        $error = "Name and email are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users_list WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user']['id']]);

        if ($stmt->fetch()) {
            $error = "Email already in use";
        } else {
            $params = [$name, $email, $phone, $address];
            $sql = "UPDATE users_list SET name = ?, email = ?, phone = ?, address = ?";

            if (!empty($password)) {
                if ($password !== $confirm) {
                    $error = "Passwords don't match";
                } elseif (strlen($password) < 8) {
                    $error = "Password must be at least 8 characters";
                } else {
                    $sql .= ", password = ?";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
            }

            if (!isset($error)) {
                $sql .= " WHERE id = ?";
                $params[] = $_SESSION['user']['id'];
                $stmt = $pdo->prepare($sql);

                if ($stmt->execute($params)) {
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['email'] = $email;
                    $_SESSION['user']['phone'] = $phone;
                    $_SESSION['user']['address'] = $address;
                    $success = "Profile updated successfully";
                } else {
                    $error = "Update failed";
                }
            }
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
        }
        .card-header {
            background: #4CAF50;
            color: white;
            border-radius: 8px 8px 0 0 !important;
            padding: 1.5rem;
        }
        .card-header h3 {
            margin: 0;
            font-size: 1.8rem;
        }
        .card-body {
            padding: 2rem;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4CAF50;
            margin-bottom: 1rem;
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
                <a href="profile.php" class="nav-link active">Profile</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </nav>
        </div>
    </div>
</header>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>My Profile</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <div class="text-center mb-4">
                        <img src="images/man.png" alt="Profile Picture" class="profile-picture">
                    </div>

                    <form method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name"
                                           value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone"
                                           value="<?= htmlspecialchars($_SESSION['user']['phone'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($_SESSION['user']['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">New Password (not mandatory)</label>
                                    <input type="password" class="form-control" name="password">
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="confirm_password">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="submit" class="btn btn-primary me-md-2 px-4">Update Profile</button>
                            <a href="index.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
