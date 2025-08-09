<?php
session_start();
require_once __DIR__ . '/database.php';

$pageTitle = "Products - Grab & Go";
$products = getProducts();

// product images
$sampleProducts = [
    'Fresh Apples' => 'apples.jpg',
    'Whole Wheat Bread' => 'bread.jpg',
    'Organic Milk' => 'milk.jpg',
    'Free Range Eggs' => 'eggs.jpg',
    'Canadian Chicken' => 'chicken.jpg',
    'Ground Beef' => 'beef.jpg'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* header styles */
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

        /* Product card styles */
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 8px;
            overflow: hidden;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-img {
            height: 200px;
            object-fit: cover;
        }
        .card-body {
            display: flex;
            flex-direction: column;
        }
        .btn-primary {
            background: #4CAF50;
            border: none;
            margin-top: auto;
            align-self: flex-start;
        }
        .btn-primary:hover {
            background: #3d8b40;
        }
        .price {
            font-weight: bold;
            color: #4CAF50;
            font-size: 1.2rem;
        }
        .products-header {
            margin: 2rem 0;
            text-align: center;
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
                <a href="products.php" class="nav-link active">Products</a>
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php" class="nav-link">Profile</a>
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
    <div class="products-header">
        <h2>Our Fresh Products</h2>
        <p class="lead">Quality groceries for your everyday needs</p>
    </div>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card product-card h-100">
                    <?php
                    // Use actual image if available
                    $imagePath = $product['image_path'] ?
                        '/uploads/products/'.htmlspecialchars($product['image_path']) :
                        'images/'.($sampleProducts[$product['name']] ?? 'default-product.jpg');
                    ?>
                    <img src="<?= $imagePath ?>"
                         class="card-img-top product-img"
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                        <p class="price">$<?= number_format($product['price'], 2) ?></p>
                        <div class="mt-auto">
                            <a href="/product.php?id=<?= $product['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>