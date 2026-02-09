<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=dummy_anakmandiri', 'root', '');

// Check users with role guru
echo "=== USERS (GURU) ===\n";
$users = $db->query('SELECT id, name, role FROM users WHERE role = "guru" LIMIT 5');
while ($row = $users->fetch(PDO::FETCH_ASSOC)) {
  echo "ID: {$row['id']}, Name: {$row['name']}, Role: {$row['role']}\n";
}

// Check guru_anak_didik
echo "\n=== GURU_ANAK_DIDIK ===\n";
$result = $db->query('SELECT id, user_id, anak_didik_id FROM guru_anak_didik LIMIT 10');
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
  echo "ID: {$row['id']}, User: {$row['user_id']}, AnakDidik: {$row['anak_didik_id']}\n";
}

// Check anak_didik
echo "\n=== ANAK_DIDIK ===\n";
$anak = $db->query('SELECT id, nama FROM anak_didiks WHERE id IN (1, 2, 3, 4, 5) LIMIT 5');
while ($row = $anak->fetch(PDO::FETCH_ASSOC)) {
  echo "ID: {$row['id']}, Nama: {$row['nama']}\n";
}
