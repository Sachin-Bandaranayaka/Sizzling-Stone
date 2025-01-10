<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Read and execute the schema.sql file
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }

    // Create default admin user with a secure password
    $defaultAdminPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $updateAdminPass = $conn->prepare("
        UPDATE users 
        SET password = :password 
        WHERE username = 'admin' AND password = '\$2y\$10\$YourHashedPasswordHere'
    ");
    $updateAdminPass->execute(['password' => $defaultAdminPassword]);

    // Create necessary directories
    $directories = [
        __DIR__ . '/public/images/menu',
        __DIR__ . '/public/images/users'
    ];

    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    echo "Setup completed successfully!\n";
    echo "Default admin credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "Please change these credentials after your first login.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
