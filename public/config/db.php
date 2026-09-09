<?php
declare(strict_types=1);
$env = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
if (is_readable($env)) foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) { $line=trim($line); if ($line==='' || str_starts_with($line,'#') || !str_contains($line,'=')) continue; [$key,$value]=explode('=',$line,2); $value=trim($value); if (strlen($value)>1 && $value[0]==='"' && substr($value,-1)==='"') $value=substr($value,1,-1); putenv(trim($key).'='.$value); }
$host=getenv('DB_HOST') ?: 'localhost'; $port=getenv('DB_PORT') ?: '3306'; $name=getenv('DB_NAME') ?: 'brgy_profiling'; $user=getenv('DB_USER') ?: 'root'; $pass=getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: '');
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]; $ca=getenv('DB_SSL_CA') ?: ''; if ($ca && is_readable($ca)) $options[PDO::MYSQL_ATTR_SSL_CA]=$ca;
$pdo=new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",$user,$pass,$options);
