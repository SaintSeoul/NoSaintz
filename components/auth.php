<?php
// Simple auth helpers for role-based pages
// Usage:
//   include 'components/auth.php';
//   require_admin(); // redirects to ../login.php if not an admin
//   require_user();  // redirects to login.php if not a logged-in user

if(session_status() === PHP_SESSION_NONE) session_start();

function require_admin(){
    // admin pages are typically in /admin/, so redirect to ../login.php
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin' || !isset($_SESSION['admin_id'])){
        header('Location: ../login.php');
        exit;
    }
}

function require_user(){
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'user' || !isset($_SESSION['user_id'])){
        header('Location: login.php');
        exit;
    }
}

function current_admin_id(){
    return $_SESSION['admin_id'] ?? null;
}

function current_user_id(){
    return $_SESSION['user_id'] ?? null;
}

?>
