<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=samfarm_db;charset=utf8', 'root', '');
    
    // Check database collation
    $stmt = $db->query("SHOW CREATE DATABASE samfarm_db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Database collation: " . $result['Create Database'] . "\n\n";
    
    // Check table collations
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW CREATE TABLE $table");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Table $table:\n" . $result['Create Table'] . "\n\n";
    }
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
