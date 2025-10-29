<?php
/**
 * Simple DB migration runner (no dependencies) - v2
 * - Tracks description per migration in __db_version.description
 * - Reads description from first comment line of each .sql (prefix: "description:" or "desc:").
 * - Falls back to filename -> description if not found.
 *
 * Usage:
 *   php scripts/migrate.php status|up|down [steps]|seed
 */

declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
$configFile = $root . '/config/database.php';
if (!file_exists($configFile)) {
    fwrite(STDERR, "Missing config/database.php. Copy config/database.php.example\n");
    exit(1);
}
$config = require $configFile;

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['DB_HOST'], $config['DB_PORT'], $config['DB_NAME'], $config['DB_CHARSET'] ?? 'utf8mb4'
);
try {
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "DB connect failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$dbName = $config['DB_NAME'];
$versionTable = '__db_version';

// Ensure version table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS `$versionTable` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(255) NOT NULL UNIQUE,
  `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Auto-upgrade: add description column if missing
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND COLUMN_NAME = 'description'");
    $stmt->execute([':db' => $dbName, ':tbl' => $versionTable]);
    $hasDesc = (int)$stmt->fetchColumn() > 0;
    if (!$hasDesc) {
        $pdo->exec("ALTER TABLE `$versionTable` ADD COLUMN `description` VARCHAR(500) NOT NULL DEFAULT '' AFTER `version`");
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Failed to ensure description column: " . $e->getMessage() . PHP_EOL);
    // continue; non-fatal
}

$cmd = $argv[1] ?? 'status';
$migrationsDir = $root . '/db/migrations';
$seedsDir = $root . '/db/seeds';

function listMigrations(string $dir): array {
    if (!is_dir($dir)) return [];
    $files = array_values(array_filter(scandir($dir), function ($f) use ($dir) {
        return preg_match('/^\d{4}_.+\.sql$/', $f) && is_file($dir . '/' . $f);
    }));
    sort($files, SORT_STRING);
    return $files;
}

function executedVersions(PDO $pdo, string $versionTable): array {
    $rows = $pdo->query("SELECT version FROM `$versionTable` ORDER BY version")->fetchAll(PDO::FETCH_COLUMN);
    return $rows ?: [];
}

function parseDescriptionFromSql(string $path, string $fallback): string {
    $h = fopen($path, 'r');
    if ($h) {
        for ($i=0; $i<20; $i++) { // scan first 20 lines
            $line = fgets($h);
            if ($line === false) break;
            $trim = trim($line);
            if (strpos($trim, '--') === 0) {
                $str = strtolower(ltrim(substr($trim, 2)));
                if (strpos($str, 'description:') === 0) {
                    fclose($h);
                    return trim(substr($trim, strlen('-- description:')));
                }
                if (strpos($str, 'desc:') === 0) {
                    fclose($h);
                    return trim(substr($trim, strlen('-- desc:')));
                }
            } elseif (strpos($trim, '/*') === 0) {
                // multi-line comment header
                $buf = $trim;
                while (strpos($buf, '*/') === false && ($l = fgets($h)) !== false) {
                    $buf .= $l;
                }
                $m = [];
                if (preg_match('/description\s*:\s*(.+?)(\*\/|$)/is', $buf, $m)) {
                    fclose($h);
                    return trim($m[1]);
                }
            } else {
                // stop if we hit SQL content
                break;
            }
        }
        fclose($h);
    }
    return $fallback;
}

function fallbackDescriptionFromFilename(string $file): string {
    $name = preg_replace('/^\d{4}_/', '', $file);
    $name = preg_replace('/\.sql$/', '', $name);
    return str_replace('_', ' ', $name);
}

function runSqlFile(PDO $pdo, string $path): void {
    $sql = file_get_contents($path);
    if ($sql === false) throw new RuntimeException("Cannot read $path");
    // naive splitter by ';' ignoring simple DELIMITER lines
    $statements = [];
    $buffer = '';
    $inDelimiter = false;
    $lines = preg_split("/\r\n|\n|\r/", $sql);
    foreach ($lines as $line) {
        $trim = trim($line);
        if (stripos($trim, 'DELIMITER') === 0) {
            $inDelimiter = !$inDelimiter;
            continue;
        }
        $buffer .= $line . "\n";
        if (!$inDelimiter && substr(rtrim($line), -1) === ';') {
            $statements[] = $buffer;
            $buffer = '';
        }
    }
    if (trim($buffer) !== '') $statements[] = $buffer;

    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') continue;
        $pdo->exec($stmt);
    }
}

function safeRollback(PDO $pdo): void {
    try {
        if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable $e) {
        // ignore
    }
}

switch ($cmd) {
    case 'status':
        $all = listMigrations($migrationsDir);
        $done = executedVersions($pdo, $versionTable);
        $pending = array_values(array_diff($all, $done));
        echo "Applied:\n";
        foreach ($done as $v) echo "  - $v\n";
        echo "Pending:\n";
        foreach ($pending as $v) {
            $path = $migrationsDir . '/' . $v;
            $desc = file_exists($path) ? parseDescriptionFromSql($path, fallbackDescriptionFromFilename($v)) : fallbackDescriptionFromFilename($v);
            echo "  - $v :: $desc\n";
        }
        break;

    case 'up':
        $all = listMigrations($migrationsDir);
        $done = executedVersions($pdo, $versionTable);
        $pending = array_values(array_diff($all, $done));
        if (!$pending) { echo "No pending migrations.\n"; break; }
        foreach ($pending as $file) {
            $path = $migrationsDir . '/' . $file;
            $desc = parseDescriptionFromSql($path, fallbackDescriptionFromFilename($file));
            echo "Applying $file ...\n";
            try {
                runSqlFile($pdo, $path);
                $stmt = $pdo->prepare("INSERT INTO `$versionTable` (`version`, `description`) VALUES (:v, :d)");
                $stmt->execute([':v' => $file, ':d' => $desc]);
                echo "OK: $file\n";
            } catch (Throwable $e) {
                safeRollback($pdo);
                fwrite(STDERR, "Failed $file: " . $e->getMessage() . PHP_EOL);
                exit(1);
            }
        }
        break;

    case 'down':
        $steps = intval($argv[2] ?? '1');
        if ($steps < 1) $steps = 1;
        $done = executedVersions($pdo, $versionTable);
        if (!$done) { echo "Nothing to roll back.\n"; break; }
        for ($i=0; $i<$steps && $done; $i++) {
            $last = array_pop($done);
            $downFile = preg_replace('/\.sql$/', '.down.sql', $last);
            $path = $GLOBALS['migrationsDir'] . '/' . $downFile;
            if (!file_exists($path)) {
                echo "Skip (no down file): $downFile\n";
                continue;
            }
            echo "Reverting $downFile ...\n";
            try {
                runSqlFile($pdo, $path);
                $stmt = $pdo->prepare("DELETE FROM `$versionTable` WHERE `version` = :v");
                $stmt->execute([':v' => $last]);
                echo "OK reverted: $last\n";
            } catch (Throwable $e) {
                safeRollback($pdo);
                fwrite(STDERR, "Failed revert $last: " . $e->getMessage() . PHP_EOL);
                exit(1);
            }
        }
        break;

    case 'seed':
        $seedPath = $seedsDir . '/seed_demo.sql';
        if (!file_exists($seedPath)) { echo "No seed file at $seedPath\n"; break; }
        echo "Seeding demo data ...\n";
        try {
            runSqlFile($pdo, $seedPath);
            echo "Seed OK.\n";
        } catch (Throwable $e) {
            fwrite(STDERR, "Seed failed: " . $e->getMessage() . PHP_EOL);
            exit(1);
        }
        break;

    default:
        echo "Unknown command: $cmd\n";
        echo "Usage: php scripts/migrate.php [status|up|down [steps]|seed]\n";
        exit(1);
}
