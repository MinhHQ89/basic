<?php
/**
 * Database Setup Script - CRUD Simple Application
 * This script initializes database and users table
 * Using PDO directly for complete independence
 */

require_once 'config.php';

try {
    echo "<h1>Database Setup - CRUD Application</h1>";
    echo "<hr>";
    
    // Step 1: Connect to MySQL (without database)
    echo "<h2>Step 1: Connecting to MySQL...</h2>";
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Connected to MySQL successfully<br>";
    
    // Step 2: Create database
    echo "<h2>Step 2: Creating database...</h2>";
    $databaseName = DB_NAME;
    $sql = "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✓ Database '{$databaseName}' created successfully<br>";
    
    // Step 3: Connect to newly created database
    echo "<h2>Step 3: Connecting to database...</h2>";
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Connected to database '{$databaseName}' successfully<br>";
    
    // Step 4: Create users table
    echo "<h2>Step 4: Creating users table...</h2>";
    $tableName = 'users';
    
    // Check if table exists
    $sql = "SHOW TABLES LIKE :table";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['table' => $tableName]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Table '{$tableName}' already exists<br>";
    } else {
        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "✓ Table '{$tableName}' created successfully<br>";
    }
    
    // Step 5: Add sample data
    echo "<h2>Step 5: Adding sample data...</h2>";
    
    // Check if data already exists
    $sql = "SELECT COUNT(*) as count FROM users";
    $stmt = $pdo->query($sql);
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // Add sample data
        $sampleData = [
            ['John Doe', 'john.doe@example.com', '+1234567890'],
            ['Jane Smith', 'jane.smith@example.com', '+0987654321'],
            ['Bob Johnson', 'bob.johnson@example.com', '+1122334455'],
            ['Alice Brown', 'alice.brown@example.com', '+5566778899'],
            ['Charlie Wilson', 'charlie.wilson@example.com', '+9988776655']
        ];
        
        $sql = "INSERT INTO users (name, email, phone) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($sampleData as $data) {
            $stmt->execute($data);
        }
        
        echo "✓ Added " . count($sampleData) . " sample users successfully<br>";
    } else {
        echo "✓ Sample data already exists ({$count} users)<br>";
    }
    
    echo "<hr>";
    echo "<h2>🎉 Setup completed successfully!</h2>";
    echo "<p>Your CRUD application is ready to use.</p>";
    echo "<p><a href='index.html'>Go to CRUD Application</a></p>";
    
} catch (Exception $e) {
    echo "<hr>";
    echo "<h2>❌ Setup failed!</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and try again.</p>";
} finally {
    if (isset($pdo)) {
        $pdo = null;
    }
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}
h1 {
    color: #333;
    text-align: center;
}
h2 {
    color: #666;
    margin-top: 20px;
}
table {
    margin-top: 10px;
    background-color: white;
}
th, td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
}
th {
    background-color: #f2f2f2;
    font-weight: bold;
}
hr {
    border: none;
    border-top: 2px solid #ddd;
    margin: 20px 0;
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
