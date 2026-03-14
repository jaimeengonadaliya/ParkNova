<?php
session_start();

$host = getenv('DB_HOST') ?: '127.0.0.1';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'parknova_db';

date_default_timezone_set('Asia/Kolkata');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// ─────────────────────────────────────────────
//  Helper Functions
// ─────────────────────────────────────────────

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/** Super Admin — full system access */
function isSuperAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

/** Backward-compat for existing admin check */
function isAdmin()
{
    return isSuperAdmin();
}

/** Parking Manager — city-scoped access */
function isManager()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
}

/** Any staff (super_admin OR manager) */
function isStaff()
{
    return isSuperAdmin() || isManager();
}

function redirect($url)
{
    header("Location: $url");
    exit();
}

// Dynamically calculate base_url to support subdirectories and root hosting
$script_name = $_SERVER['SCRIPT_NAME'];
$dir = dirname($script_name);
$base_url = ($dir === DIRECTORY_SEPARATOR || $dir === '/') ? '' : str_replace('\\', '/', $dir);

// If we are in a subfolder like /ParkNova/ajax or /ParkNova/admin, we need the root of the project
if (strpos($base_url, '/ParkNova') !== false) {
    $base_url = substr($base_url, 0, strpos($base_url, '/ParkNova') + 9);
}
else {
    // For root hosting or other folder names, assume the first segment after root
    $parts = explode('/', trim($base_url, '/'));
    if (!empty($parts[0])) {
        $base_url = '/' . $parts[0];
    }
    else {
        $base_url = '';
    }
}

function getCityImage($city)
{
    $city = strtolower(trim($city));
    $images = [
        'ahmedabad' => 'https://images.unsplash.com/photo-1625471644753-33230623ae1c?auto=format&fit=crop&q=80&w=800',
        'surat'     => 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?auto=format&fit=crop&q=80&w=800',
        'mumbai'    => 'https://images.unsplash.com/photo-1566552881510-bd019795d28b?auto=format&fit=crop&q=80&w=800',
        'pune'      => 'https://images.unsplash.com/photo-1584893717470-34863c0a59f6?auto=format&fit=crop&q=80&w=800',
        'delhi'     => 'https://images.unsplash.com/photo-1587474260584-1b3574e91819?auto=format&fit=crop&q=80&w=800',
        'bangalore' => 'https://images.unsplash.com/photo-1596701062351-8c2c14d1fcd0?auto=format&fit=crop&q=80&w=800',
    ];
    return $images[$city] ?? 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&q=80&w=800';
}
?>



