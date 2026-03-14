<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Sesuaikan dengan username MySQL Anda
define('DB_PASS', '');      // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'portal_berita');

// Konfigurasi Website
define('SITE_URL', 'http://localhost/portal-berita');
define('SITE_NAME', 'Portal Berita UMKM');
define('SITE_DESCRIPTION', 'Portal berita dan informasi terkini untuk UMKM Indonesia');

// Koneksi Database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Fungsi Helper
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function create_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function format_date($date) {
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

// Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
