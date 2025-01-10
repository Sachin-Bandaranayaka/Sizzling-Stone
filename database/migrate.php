<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Function to check if column exists
    function columnExists($conn, $table, $column) {
        $sql = "SHOW COLUMNS FROM {$table} LIKE '{$column}'";
        $result = $conn->query($sql);
        return $result->rowCount() > 0;
    }

    // Add new columns to users table
    $table = "users";
    
    // Add name column
    if (!columnExists($conn, $table, 'name')) {
        $conn->exec("ALTER TABLE users ADD COLUMN name VARCHAR(100) AFTER email");
        echo "Added name column\n";
    }

    // Add is_active column
    if (!columnExists($conn, $table, 'is_active')) {
        $conn->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER role");
        echo "Added is_active column\n";
    }

    // Add updated_at column
    if (!columnExists($conn, $table, 'updated_at')) {
        $conn->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        echo "Added updated_at column\n";
    }

    // First, add a temporary role column
    if (!columnExists($conn, $table, 'new_role')) {
        $conn->exec("ALTER TABLE users ADD COLUMN new_role VARCHAR(10) DEFAULT 'user'");
        echo "Added temporary role column\n";

        // Copy existing roles to new column
        $conn->exec("UPDATE users SET new_role = 'admin' WHERE role = 'admin'");
        $conn->exec("UPDATE users SET new_role = 'user' WHERE role != 'admin' OR role IS NULL");
        echo "Copied roles to temporary column\n";

        // Drop the old role column
        $conn->exec("ALTER TABLE users DROP COLUMN role");
        echo "Dropped old role column\n";

        // Add the new ENUM role column
        $conn->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER phone");
        echo "Added new role column\n";

        // Copy roles from temporary column
        $conn->exec("UPDATE users SET role = new_role");
        echo "Copied roles to new column\n";

        // Drop the temporary column
        $conn->exec("ALTER TABLE users DROP COLUMN new_role");
        echo "Dropped temporary column\n";
    }

    // Update existing users to set name = username if name is NULL
    $conn->exec("UPDATE users SET name = username WHERE name IS NULL");
    echo "Updated NULL names to usernames\n";

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
