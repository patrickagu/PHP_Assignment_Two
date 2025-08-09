<?php
session_start();
require_once __DIR__ . '/CRUD.php';
require_once __DIR__ . '/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Initialize CRUD
$crud = new CRUD();

// Example: Get user's shopping cart
$userId = $_SESSION['user']['id'];
$cartItems = $crud->read('shopping_cart', ['user_id' => $userId]);

// Example: Add item to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Check if item already exists in cart
    if ($crud->exists('shopping_cart', ['user_id' => $userId, 'product_id' => $productId])) {
        // Update quantity
        $crud->update('shopping_cart',
            ['quantity' => $quantity],
            ['user_id' => $userId, 'product_id' => $productId]
        );
    } else {
        // Add new item
        $crud->create('shopping_cart', [
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    header("Location: cart.php");
    exit();
}

// Example: Remove item from cart
if (isset($_GET['remove_item'])) {
    $itemId = $_GET['remove_item'];
    $crud->delete('shopping_cart', ['id' => $itemId, 'user_id' => $userId]);
    header("Location: cart.php");
    exit();
}