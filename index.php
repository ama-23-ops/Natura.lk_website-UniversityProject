<?php
session_start();
include_once 'db.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
  // Check if the user is an admin
  if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    // Redirect to the admin dashboard
    header("Location: admin/dashboard.php");
    exit();
  } else {
    // Redirect to the customer dashboard
    header("Location: customer/dashboard.php");
    exit();
  }
} else {
  // If not logged in, redirect to the home page
  header("Location: home.php");
  exit();
}
?>