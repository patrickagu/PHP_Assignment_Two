<?php
// Database configuration
define('DB_HOST', '172.31.22.43');
define('DB_NAME', 'Patrick200626972');
define('DB_USER', 'Patrick200626972');
define('DB_PASS', 'hJQ58RlEST');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// functions
function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function isAdmin()
{
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

function redirect($url) {
    // Get the base path of your application
    $basePath = dirname($_SERVER['PHP_SELF']);

    // Remove trailing slash if exists
    if ($basePath === '/') {
        $basePath = '';
    }

    // Construct the full URL
    $fullUrl = $basePath . $url;

    header("Location: $fullUrl");
    exit();
}

function uploadProductImage($file)
{
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $uploadDir = __DIR__ . '/uploads/products/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $mime = mime_content_type($file['tmp_name']);

    if (!array_key_exists($mime, $allowed)) return null;

    $ext = $allowed[$mime];
    $filename = uniqid() . '.' . $ext;
    $target = $uploadDir . $filename;

    return move_uploaded_file($file['tmp_name'], $target) ? $filename : null;
}

function getProducts()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProduct($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserByEmail($email)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users_list WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createUser($name, $email, $password, $phone = null, $address = null, $payment_method = null, $location = null, $role = 'user') {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users_list 
            (name, email, password, phone, address, payment_method, location, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $name,
            $email,
            $hash,
            $phone,
            $address,
            $payment_method,
            $location,
            $role
        ]);
    } catch (PDOException $e) {
        error_log("User creation failed: " . $e->getMessage());
        return false;
    }
}

function emailExists($email)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users_list WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}
