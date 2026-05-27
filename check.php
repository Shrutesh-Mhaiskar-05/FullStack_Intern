<?php
echo "<h2>Portfolio Setup Check</h2>";

// PHP version
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Check if MySQL extension is available
if (extension_loaded('pdo_mysql')) {
    echo "<p style='color:green'>✓ PDO MySQL extension is loaded</p>";
} else {
    echo "<p style='color:red'>✗ PDO MySQL extension is NOT loaded. Enable it in php.ini (uncomment: extension=pdo_mysql)</p>";
}

// Try connecting to MySQL
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $testConn = new PDO("mysql:host=$host", $username, $password);
    echo "<p style='color:green'>✓ MySQL connection successful</p>";

    // Check if database exists
    $stmt = $testConn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'portfolioweb'");
    if ($stmt->fetch()) {
        echo "<p style='color:green'>✓ Database 'portfolioweb' exists</p>";

        // Check if table exists
        $testConn->exec("USE portfolioweb");
        $stmt = $testConn->query("SHOW TABLES LIKE 'messages'");
        if ($stmt->fetch()) {
            echo "<p style='color:green'>✓ Table 'messages' exists</p>";
        } else {
            echo "<p style='color:red'>✗ Table 'messages' does not exist. Run schema.sql</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Database 'portfolioweb' does not exist. Run schema.sql</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>✗ MySQL connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running. If using XAMPP, start MySQL from the control panel.</p>";
}

echo "<hr><p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Open XAMPP Control Panel → Start Apache & MySQL</li>";
echo "<li>Open phpMyAdmin, import <code>schema.sql</code></li>";
echo "<li>Place all files in <code>htdocs/portfolio/</code> folder</li>";
echo "<li>Visit <code>http://localhost/portfolio/index.html</code></li>";
echo "</ol>";
