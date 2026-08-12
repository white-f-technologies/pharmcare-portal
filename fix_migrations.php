<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pharm_care', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Drop tables that might exist from failed migration batch
    $tables = ['sale_items', 'sales', 'prescription_items', 'prescriptions'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "Dropped table: $table\n";
    }

    // Clean up pending migration from migrations table (in case of partial entries)
    $pdo->exec("DELETE FROM migrations WHERE migration LIKE '%create_sales_table%'");
    $pdo->exec("DELETE FROM migrations WHERE migration LIKE '%create_sale_items_table%'");
    $pdo->exec("DELETE FROM migrations WHERE migration LIKE '%create_prescriptions%'");

    echo "Cleanup complete. Ready to re-run migrations.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
