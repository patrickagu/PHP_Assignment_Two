<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Home - Grab & Go";
$pageDesc = "Your one-stop supermarket for all daily needs";
?>

require_once __DIR__ . '/header.php';

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

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('images/supermarket.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 8rem 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
        }

        .hero h2 {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        /* Category Grid */
        .featured-categories {
            padding: 4rem 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .featured-categories h3 {
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 2rem;
            color: #333;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            padding: 0 20px;
        }

        .category-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: #333;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .category-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .category-card h4 {
            padding: 1.2rem;
            text-align: center;
            margin: 0;
            font-size: 1.2rem;
        }

        /* Button Styles */
        .btn {
            padding: 0.8rem 1.8rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
            border: 2px solid #4CAF50;
        }

        .btn-primary:hover {
            background: #3d8b40;
            border-color: #3d8b40;
        }

        .btn-outline {
            border: 2px solid white;
            color: white;
        }

        .btn-outline:hover {
            background: white;
            color: #4CAF50;
            border-color: white;
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
            <a href="index.php" class="nav-link active">Home</a>
            <a href="products.php" class="nav-link">Products</a>
            <?php if (isset($_SESSION['user'])): ?>
                <?php if ($_SESSION['user']['is_admin'] ?? false): ?>
                    <a href="admin.php" class="nav-link">Admin</a>
                <?php endif; ?>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="homepage">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Fresh Groceries Delivered to Your Door</h2>
            <p>Shop our wide selection of quality products at affordable prices</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary">Shop Now</a>
                <?php if (!isset($_SESSION['user'])): ?>
                    <a href="register.php" class="btn btn-outline">Sign Up</a>
                <?php else: ?>
                    <a href="upload_item.php" class="btn btn-outline">Request Item</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="featured-categories">
        <h3>Shop by Category</h3>
        <div class="category-grid">
            <a href="products.php?category=fresh" class="category-card">
                <img src="images/fresh.jpeg" alt="Fresh Produce">
                <h4>Fresh Produce</h4>
            </a>
            <a href="products.php?category=dairy" class="category-card">
                <img src="images/dairy.jpg" alt="Dairy & Eggs">
                <h4>Dairy & Eggs</h4>
            </a>
            <a href="products.php?category=meat" class="category-card">
                <img src="images/meat.jpg" alt="Meat & Poultry">
                <h4>Meat & Poultry</h4>
            </a>
            <a href="products.php?category=bakery" class="category-card">
                <img src="images/bakery.jpg" alt="Bakery">
                <h4>Bakery</h4>
            </a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
