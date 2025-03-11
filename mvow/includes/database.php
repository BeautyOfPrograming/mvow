<?php
$host = 'localhost';
$dbname = 'mvow';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


// --- SANITIZATION FUNCTION MUST BE HERE ---
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}



function redirect($url) {
    header("Location: $url");
    exit();
}


?>