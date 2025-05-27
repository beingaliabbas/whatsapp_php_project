<?php
require 'db.php'; // Your DB connection (PDO in your case)

$filename = 'database_backup_' . date('Y-m-d_H-i-s') . '.txt';
$file = fopen($filename, 'w');

// Get all table names
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    fwrite($file, "===== TABLE: $table =====\n");

    // Get columns
    $columnsStmt = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
    fwrite($file, implode("\t", $columns) . "\n");

    // Get data
    $dataStmt = $conn->query("SELECT * FROM `$table`");
    while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
        $line = implode("\t", array_map(fn($v) => is_null($v) ? 'NULL' : $v, $row));
        fwrite($file, $line . "\n");
    }

    fwrite($file, "\n\n");
}

fclose($file);
echo "✅ Backup saved to $filename";
?>
