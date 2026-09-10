<?php
// Lecture simple du fichier .env sans dépendance externe
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $db   = $env['DB_NAME'] ?? 'vite_et_gourmand';
    $user = $env['DB_USER'] ?? 'root';
    $pass = $env['DB_PASS'] ?? '';
} else {
    // Valeurs par défaut si .env absent
    $host = '127.0.0.1';
    $db   = 'vite_et_gourmand';
    $user = 'root';
    $pass = '';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
