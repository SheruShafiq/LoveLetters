<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => true,
    'cookie_httponly' => true,
]);

header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require 'vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = 'db';
$db = $_ENV['POSTGRES_DB'];
$user = $_ENV['POSTGRES_USER'];
$pass = $_ENV['POSTGRES_PASSWORD'];
$dsn = "pgsql:host=$host;port=5432;dbname=$db;";

try {
    $conn = new PDO($dsn, $user, $pass);
    createUsersTable($conn);
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($_SERVER['REQUEST_URI'] === '/view-accounts') {
            viewAccounts($conn);
        }
        break;
    case 'POST':
        if ($_SERVER['REQUEST_URI'] === '/create-account') {
            createAccount($conn);
        } elseif ($_SERVER['REQUEST_URI'] === '/login') {
            login($conn);
        } elseif ($_SERVER['REQUEST_URI'] === '/create-multiple-accounts') {
            createMultipleAccounts($conn);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}

function createUsersTable($conn)
{
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL
        );
    ";
    $conn->exec($createTableQuery);
}

function viewAccounts($conn)
{
    if (!isset($_SESSION['username'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username FROM users");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
    exit;
}

function createAccount($conn)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $username = filter_var($input['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = password_hash($input['password'], PASSWORD_BCRYPT);

    if (usernameExists($conn, $username)) {
        http_response_code(403);
        echo json_encode(['error' => 'Username already exists']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Account created successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create account']);
    }
    exit;
}

function createMultipleAccounts($conn)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $accounts = $input['accounts'];

    foreach ($accounts as $account) {
        $username = filter_var($account['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = password_hash($account['password'], PASSWORD_BCRYPT);

        if (usernameExists($conn, $username)) {
            echo json_encode(['error' => "Username $username already exists"]);
            continue;
        }

        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            echo json_encode(['message' => "Account for $username created successfully"]);
        } else {
            echo json_encode(['error' => "Failed to create account for $username"]);
        }
    }
    exit;
}

function login($conn)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $username = filter_var($input['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $input['password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['username'] = $username;
        setcookie('PHPSESSID', session_id(), time() + (86400 * 30), "/", "", true, true);
        echo json_encode(['message' => 'Login successful']);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid username or password']);
    }
    exit;
}

function usernameExists($conn, $username)
{
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}
