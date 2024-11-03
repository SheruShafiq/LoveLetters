<?php
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
} catch (PDOException $e) {
    echo $e->getMessage();
}


