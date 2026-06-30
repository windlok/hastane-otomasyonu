<?php
/**
 * Veritabanı Migration Çalıştırıcı
 * migrations/ klasöründeki SQL dosyalarını sırayla uygular
 */

require_once 'bagla.php';

echo "=== Hastane Otomasyonu Migration Başlatılıyor ===\n\n";

function migration_exec(PDO $db, string $query): bool
{
    $query = trim($query);
    if ($query === '' || str_starts_with($query, '--')) {
        return false;
    }

    try {
        $db->exec($query);
        return true;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        $skipCodes = ['42S01', '42S21', '23000', '42000', '1060', '1061', '1062'];

        if (in_array($e->getCode(), $skipCodes, true)
            || str_contains($msg, 'already exists')
            || str_contains($msg, 'Duplicate column')
            || str_contains($msg, 'Duplicate key name')
            || str_contains($msg, 'Duplicate entry')) {
            echo "⊘ Atlandı: " . substr(str_replace("\n", ' ', $query), 0, 70) . "...\n";
            return false;
        }

        throw $e;
    }
}

try {
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration_name VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $migrationDir = __DIR__ . '/migrations';
    $files = glob($migrationDir . '/*.sql');
    sort($files);

    if (!$files) {
        throw new Exception('migrations/ klasöründe SQL dosyası bulunamadı!');
    }

    $executed = 0;
    $skippedMigrations = 0;

    foreach ($files as $file) {
        $name = basename($file, '.sql');

        $check = $db->prepare('SELECT id FROM migrations WHERE migration_name = ?');
        $check->execute([$name]);
        if ($check->fetch()) {
            echo "→ $name (zaten uygulanmış)\n";
            $skippedMigrations++;
            continue;
        }

        echo "\n--- $name ---\n";
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new Exception("$name dosyası okunamadı!");
        }

        $queries = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($queries as $query) {
            if (migration_exec($db, $query)) {
                echo "✓ " . substr(str_replace("\n", ' ', $query), 0, 70) . "...\n";
                $executed++;
            }
        }

        $insert = $db->prepare('INSERT INTO migrations (migration_name) VALUES (?)');
        $insert->execute([$name]);
    }

    echo "\n=== Migration Tamamlandı ===\n";
    echo "Çalıştırılan sorgu sayısı: $executed\n";
    echo "Atlanan migration: $skippedMigrations\n";

    $stmt = $db->query('SELECT migration_name, executed_at FROM migrations ORDER BY executed_at');
    $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($migrations) {
        echo "\n=== Yüklü Migrationlar ===\n";
        foreach ($migrations as $m) {
            echo "- {$m['migration_name']} ({$m['executed_at']})\n";
        }
    }

    try {
        $doktorSayisi = (int) $db->query('SELECT COUNT(*) FROM doktor')->fetchColumn();
        if ($doktorSayisi === 0) {
            echo "\n--- seed_doktorlar ---\n";
            require __DIR__ . '/seed_doktorlar.php';
        }
    } catch (PDOException $e) {
        echo "⊘ Doktor seed atlandı: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "\n✗ HATA: " . $e->getMessage() . "\n";
    exit(1);
}
