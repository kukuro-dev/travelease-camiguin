<?php
// Base URL for building absolute paths (links, images, assets)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/camiguin-rental');
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "tourist_system_db"; // ✅ correct database name

$conn = new mysqli($host, $user, $pass, $db);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
?>