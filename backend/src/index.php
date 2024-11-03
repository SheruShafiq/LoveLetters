<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

header('Content-Type: application/json');
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$host = 'db';
$db   = $_ENV['POSTGRES_DB'];
$user = $_ENV['POSTGRES_USER'];
$pass = $_ENV['POSTGRES_PASSWORD'];
$dsn = "pgsql:host=$host;port=5432;dbname=$db;";

try {
    $conn = new PDO($dsn, $user, $pass);
    echo "Connected to the database successfully!";

    // Create users table if it doesn't exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL
        );
    ";
    $conn->exec($createTableQuery);
} catch (PDOException $e) {
    echo $e->getMessage();
}



if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/view-accounts') {
    $stmt = $conn->prepare("SELECT id, username FROM users");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/create-account') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'];
    $password = password_hash($input['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Account created successfully']);
    } else {
        echo json_encode(['error' => 'Failed to create account']);
    }
    exit;
}

?>
