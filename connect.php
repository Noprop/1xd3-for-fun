<?php
$driver = strtolower(getenv('GECKODE_DB_DRIVER') ?: 'sqlite');

try {
  if ($driver === 'mysql') {
    $host = getenv('GECKODE_DB_HOST') ?: 'localhost';
    $port = getenv('GECKODE_DB_PORT') ?: '3306';
    $dbname = getenv('GECKODE_DB_NAME') ?: 'geckode';
    $username = getenv('GECKODE_DB_USER') ?: 'root';
    $password = getenv('GECKODE_DB_PASSWORD') ?: '';

    $pdo = new PDO(
      "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
      $username,
      $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
      user_id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(255) NOT NULL UNIQUE,
      email VARCHAR(255) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      display_name VARCHAR(255) NOT NULL,
      profile_picture_url VARCHAR(500) DEFAULT NULL,
      user_role VARCHAR(20) NOT NULL DEFAULT 'student',
      date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      date_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
  } else {
    $sqlitePath = getenv('GECKODE_SQLITE_PATH') ?: __DIR__ . '/data/geckode.sqlite';
    $sqliteDirectory = dirname($sqlitePath);

    if (!is_dir($sqliteDirectory) && !mkdir($sqliteDirectory, 0775, true) && !is_dir($sqliteDirectory)) {
      die('Connection failed: unable to create the SQLite data directory.');
    }

    $pdo = new PDO("sqlite:" . $sqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
      user_id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT NOT NULL UNIQUE,
      email TEXT NOT NULL UNIQUE,
      password_hash TEXT NOT NULL,
      display_name TEXT NOT NULL,
      profile_picture_url TEXT DEFAULT NULL,
      user_role TEXT NOT NULL DEFAULT 'student',
      date_created TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
      date_updated TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("INSERT OR IGNORE INTO users (
      username,
      email,
      password_hash,
      display_name,
      profile_picture_url,
      user_role
    ) VALUES (
      'geckode-demo',
      'demo@geckode.local',
      '" . password_hash('Geckode123!', PASSWORD_DEFAULT) . "',
      'Demo Student',
      NULL,
      'student'
    )");
  }
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}
