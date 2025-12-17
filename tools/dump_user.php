<?php
$env = [];
$lines = file(__DIR__ . '/../.env');
foreach ($lines as $line) {
  $line = trim($line);
  if (!$line || strpos($line, '#') === 0) continue;
  [$k, $v] = explode('=', $line, 2) + [null, null];
  if ($k) $env[trim($k)] = trim($v);
}
$host = $env['DB_HOST'] ?? '127.0.0.1';
$db = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$id = $argv[1] ?? 18;
try {
  $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $r = $stmt->fetch(PDO::FETCH_ASSOC);
  var_export($r);
} catch (Exception $e) {
  echo 'ERROR: ' . $e->getMessage() . "\n";
}
