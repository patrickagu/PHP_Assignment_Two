<?php
session_start();

// Set success message before destroying session
$_SESSION['success'] = "You have been successfully logged out.";

// Clear all session data
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();