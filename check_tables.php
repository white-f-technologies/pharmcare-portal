<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pharm_care', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in pharm_care:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
