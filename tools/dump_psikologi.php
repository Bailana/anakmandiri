<?php
$dotenv = __DIR__.'/../vendor/autoload.php';
require $dotenv;
$env = [];
$lines = file(__DIR__.'/../.env');
foreach ($lines as $line) {
    $line = trim($line);
    if (!$line || strpos($line, '#') === 0) continue;
    [$k,$v] = explode('=', $line, 2) + [null,null];
    if ($k) $env[trim($k)] = trim($v);
}
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$db = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query('SELECT id, anak_didik_id, user_id, created_at, updated_at, latar_belakang, metode_assessment, hasil_assessment, kesimpulan, rekomendasi, diagnosa_psikologi FROM program_psikologi ORDER BY created_at DESC LIMIT 10');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "id={$r['id']}, anak_didik_id={$r['anak_didik_id']}, user_id={$r['user_id']}, created_at={$r['created_at']}, diagnosa_psikologi={$r['diagnosa_psikologi']}\n";
    }
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
