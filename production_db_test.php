<?php
// Production Database Test Script
// Upload this to your production server to test database connection

header('Content-Type: text/plain');
echo "=== AGCO Production Database Test ===\n\n";

// Try to load Laravel environment
$envFile = __DIR__ . '/core/.env';
if (file_exists($envFile)) {
    echo "✅ .env file found\n";
    
    // Parse .env file manually (simplified)
    $env = [];
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
    
    echo "Database Configuration:\n";
    echo "Host: " . ($env['DB_HOST'] ?? 'Not set') . "\n";
    echo "Database: " . ($env['DB_DATABASE'] ?? 'Not set') . "\n";
    echo "Username: " . ($env['DB_USERNAME'] ?? 'Not set') . "\n";
    echo "Password: " . (empty($env['DB_PASSWORD']) ? 'Not set' : str_repeat('*', strlen($env['DB_PASSWORD']))) . "\n";
    echo "Port: " . ($env['DB_PORT'] ?? 'Not set') . "\n\n";
    
    // Test database connection
    try {
        $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4";
        $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);
        
        echo "✅ Database connection successful!\n\n";
        
        // Get MySQL version
        $version = $pdo->query("SELECT VERSION() as version")->fetch();
        echo "MySQL Version: " . $version['version'] . "\n\n";
        
        // Check tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables found: " . count($tables) . "\n";
        
        if (count($tables) > 0) {
            echo "First 10 tables:\n";
            foreach (array_slice($tables, 0, 10) as $table) {
                echo "  - $table\n";
            }
        }
        
        // Check users table if exists
        if (in_array('users', $tables)) {
            $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch();
            echo "\nUsers table records: " . $userCount['count'] . "\n";
        }
        
        echo "\n✅ Production database is working!\n";
        
    } catch (PDOException $e) {
        echo "❌ Database connection failed!\n";
        echo "Error: " . $e->getMessage() . "\n\n";
        
        echo "Possible issues:\n";
        echo "1. Database server not running\n";
        echo "2. Incorrect database credentials\n";
        echo "3. Database doesn't exist\n";
        echo "4. User permissions insufficient\n";
    }
    
} else {
    echo "❌ .env file not found\n";
    echo "Current directory: " . __DIR__ . "\n";
    echo "Files in current directory:\n";
    
    $files = scandir(__DIR__);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
}

echo "\n=== Test Complete ===\n";
?>
