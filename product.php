<?php
if (!isset($_GET['id'])) {
    redirect('/products.php');
}

$product = getProduct($_GET['id']);
if (!$product) {
    redirect('/products.php');
}

$pageTitle = $product['name'];
?>

<div class="row">
    <div class="col-md-6">
        <?php if ($product['image_path']): ?>
            <img src="/uploads/products/<?= htmlspecialchars($product['image_path']) ?>"
                 class="img-fluid"
                 alt="<?= htmlspecialchars($product['name']) ?>">
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <h3 class="text-primary">$<?= number_format($product['price'], 2) ?></h3>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

        <?php if (isAdmin()): ?>
            <div class="mt-4">
                <a href="/admin.php?edit=<?= $product['id'] ?>" class="btn btn-warning">Edit</a>
                <a href="/admin.php?delete=<?= $product['id'] ?>" class="btn btn-danger"
                   onclick="return confirm('Delete this product?')">Delete</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
