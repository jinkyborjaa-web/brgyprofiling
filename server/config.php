<?php
$envFile = __DIR__ . '/../.env';

if (is_readable($envFile)) {
	foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		$line = trim($line);

		if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
			continue;
		}

		[$name, $value] = explode('=', $line, 2);
		$name = trim($name);
		$value = trim($value);

		if ($value !== '' && $value[0] === '"' && substr($value, -1) === '"') {
			$value = substr($value, 1, -1);
		}

		putenv("$name=$value");
	}
}

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$db = getenv('DB_NAME') ?: 'brgy_profiling';
$caPath = getenv('DB_SSL_CA') ?: '';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

if ($caPath !== '' && is_readable($caPath)) {
	$options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
}

$pdo = new PDO($dsn, $user, $pass, $options);
?>
