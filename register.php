<?php
// Session and error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/header.php';

// Ensure redirect function exists
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = "Register - Grab & Go";
$error = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Optional fields
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $location = trim($_POST['location'] ?? '');

    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, email and password are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm) {
        $error = "Passwords don't match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (emailExists($email)) {
        $error = "Email already registered";
    } else {
        // Attempt to create user
        if (createUser($name, $email, $password, $phone, $address, $payment_method, $location, 'user')) {
            $_SESSION['success'] = "Registration successful! Please login.";
            redirect('login.php');
        } else {
            $error = "Registration failed. Please try again.";
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
            border-radius: 8px 8px 0 0;
            padding: 1.5rem;
        }
        .card-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        .card-body {
            padding: 2rem;
        }
        .optional-field {
            color: #6c757d;
            font-style: italic;
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
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link active">Register</a>
            </nav>
        </div>
    </div>
</header>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h2>Create Your Account</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" required>
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label optional-field">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label optional-field">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label optional-field">Preferred Payment Method</label>
                                    <select class="form-select" name="payment_method">
                                        <option value="">Select payment method</option>
                                        <option value="credit_card" <?= ($_POST['payment_method'] ?? '') === 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
                                        <option value="paypal" <?= ($_POST['payment_method'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                                        <option value="bank_transfer" <?= ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label optional-field">Location</label>
                                    <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="submit" class="btn btn-primary me-md-2 px-4">Register</button>
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
