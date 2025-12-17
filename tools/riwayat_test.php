<?php
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
    $anak = $argv[1] ?? 30;
    $res = [];
    $tables = ['program_wicara'=>'wicara','program_si'=>'si','program_psikologi'=>'psikologi'];
    foreach ($tables as $table=>$sumber) {
        $stmt = $pdo->prepare("SELECT id,user_id,created_at,updated_at FROM $table WHERE anak_didik_id = :aid ORDER BY created_at DESC");
        $stmt->execute([':aid'=>$anak]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $p) {
            $tanggalStr = is_string($p['created_at']) ? $p['created_at'] : ($p['created_at'] ? $p['created_at']->format('Y-m-d') : null);
            if ($p['created_at']) {
                $date = date('Y-m-d', strtotime($p['created_at']));
                $hari = date('l', strtotime($date));
                // convert English day to Indonesian
                $map = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
                $hariInd = $map[$hari] ?? $hari;
                $tgl = date('d-m-Y', strtotime($date));
            } else {
                $hariInd = null; $tgl = null;
            }
            $res[] = ['id'=>$p['id'],'sumber'=>$sumber,'hari'=>$hariInd,'tanggal'=>$tgl,'created_at'=>$p['created_at']];
        }
    }
    echo json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
