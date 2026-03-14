<?php
require_once '../includes/config.php';

// Hapus semua session
session_destroy();

// Redirect ke login
redirect('login.php');
?>
