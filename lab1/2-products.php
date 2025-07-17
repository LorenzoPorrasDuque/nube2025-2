<?php
header('Access-Control-Allow-Origin: *'); // O reemplaza * por el dominio exacto
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');
// Connect to the database
define('DB_HOST', 'localhost');
define('DB_NAME', 'prueba');
define('DB_CHARSET', 'utf8');
define('DB_USER', 'prueba');
define('DB_PASSWORD', 'prueba');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET . ";dbname=" . DB_NAME,
        DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (Exception $ex) {
    die($ex->getMessage());
}

// Fetch products
$stmt = $pdo->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll();

// Output products in JSON format
header('Content-Type: application/json');
echo json_encode($products);
?>

