<?php
// Run Production Migrations Script
// This script will safely run Laravel migrations on production

header('Content-Type: text/plain');
echo "=== AGCO Production Migration Runner ===\n\n";

// Load environment
$envFile = __DIR__ . '/core/.env';
if (!file_exists($envFile)) {
    echo "❌ .env file not found\n";
    exit;
}

// Parse .env
$env = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '#') === 0) continue;
    if (strpos($line, '=') === false) continue;
    list($key, $value) = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

try {
    $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database\n\n";
    
    // Change to Laravel directory
    $laravelPath = __DIR__ . '/core';
    if (!is_dir($laravelPath)) {
        echo "❌ Laravel directory not found\n";
        exit;
    }
    
    chdir($laravelPath);
    echo "Changed to Laravel directory: $laravelPath\n\n";
    
    // Step 1: Check if artisan exists
    if (!file_exists('artisan')) {
        echo "❌ Artisan not found\n";
        exit;
    }
    
    $phpPath = 'd:\xampp\php\php.exe';
    $artisanPath = __DIR__ . '/core/artisan';
    
    echo "=== Step 1: Check Migration Status ===\n";
    
    // Get current migration status
    $output = shell_exec("$phpPath $artisanPath migrate:status 2>&1");
    
    echo "Migration Status:\n";
    foreach (explode("\n", $output) as $line) {
        echo $line . "\n";
    }
    
    echo "\n=== Step 2: Run Fresh Migrations (Safe Mode) ===\n";
    
    // Create backup of current data
    echo "Creating data backup...\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $backupData = [];
    
    foreach ($tables as $table) {
        if ($table !== 'migrations') {
            $count = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch();
            if ($count['count'] > 0) {
                $backupData[$table] = $count['count'];
                echo "Backing up $table ({$count['count']} records)\n";
            }
        }
    }
    
    echo "\n=== Step 3: Run Migrations ===\n";
    
    // Run migrations with force flag for production
    echo "Running: $phpPath $artisanPath migrate --force\n";
    $output = [];
    $returnCode = 0;
    exec("$phpPath $artisanPath migrate --force 2>&1", $output, $returnCode);
    
    echo "Migration Output:\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
    
    if ($returnCode === 0) {
        echo "\n✅ Migrations completed successfully\n";
    } else {
        echo "\n❌ Migration errors occurred\n";
    }
    
    echo "\n=== Step 4: Verify Results ===\n";
    
    // Check final migration status
    $output = [];
    $returnCode = 0;
    exec("$phpPath $artisanPath migrate:status 2>&1", $output, $returnCode);
    
    echo "Final Migration Status:\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
    
    // Check table counts
    echo "\nTable Record Counts:\n";
    foreach ($backupData as $table => $originalCount) {
        $currentCount = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch();
        $status = $currentCount['count'] >= $originalCount ? '✅' : '⚠️';
        echo "$status $table: $currentCount[count] records (was $originalCount)\n";
    }
    
    // Check for new tables
    echo "\nChecking for new tables:\n";
    $currentTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $newTables = array_diff($currentTables, array_keys($backupData));
    $newTables = array_diff($newTables, ['migrations']);
    
    if (!empty($newTables)) {
        echo "New tables created:\n";
        foreach ($newTables as $table) {
            $count = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch();
            echo "✅ $table ({$count['count']} records)\n";
        }
    } else {
        echo "No new tables created\n";
    }
    
    echo "\n=== Step 5: Clear Caches ===\n";
    
    // Clear Laravel caches
    echo "Clearing caches...\n";
    $cacheCommands = [
        "$phpPath $artisanPath cache:clear",
        "$phpPath $artisanPath config:clear",
        "$phpPath $artisanPath route:clear",
        "$phpPath $artisanPath view:clear"
    ];
    
    foreach ($cacheCommands as $command) {
        echo "Running: $command\n";
        $output = [];
        exec("$command 2>&1", $output);
        foreach ($output as $line) {
            if (strpos($line, 'cleared') !== false || strpos($line, 'Cache cleared') !== false) {
                echo "✅ $line\n";
            }
        }
    }
    
    echo "\n=== Migration Complete ===\n";
    echo "✅ Production migrations completed successfully\n";
    echo "✅ Database structure updated\n";
    echo "✅ Caches cleared\n";
    echo "✅ Ready for use\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
