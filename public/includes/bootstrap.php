<?php
declare(strict_types=1);
if (session_status()!==PHP_SESSION_ACTIVE) session_start();
require_once __DIR__.'/../config/db.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
function h(mixed $v,string $fallback=''):string { $v=trim((string)$v); return htmlspecialchars($v===''?$fallback:$v,ENT_QUOTES,'UTF-8'); }
function csrf():string { return h($_SESSION['csrf_token'] ?? ''); }
function check_csrf():void { if (!hash_equals($_SESSION['csrf_token'] ?? '',(string)($_POST['csrf_token'] ?? ''))) { http_response_code(419); exit('Invalid security token.'); } }
function logged_in():bool { return isset($_SESSION['user']); }
function require_login(string $intent=''):void { if (!logged_in()) { $redirect=$_SERVER['REQUEST_URI'] ?? 'dashboard.php'; header('Location: login.php?'.http_build_query(['redirect'=>$redirect,'intent'=>$intent])); exit; } }
function rows(PDO $pdo,string $sql,array $params=[]):array { try { $s=$pdo->prepare($sql); $s->execute($params); return $s->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); return []; } }
function value(PDO $pdo,string $sql,array $params=[],mixed $fallback=0):mixed { try { $s=$pdo->prepare($sql); $s->execute($params); $v=$s->fetchColumn(); return $v===false?$fallback:$v; } catch(Throwable $e) { error_log($e->getMessage()); return $fallback; } }
function ref_code(string $prefix):string { return $prefix.'-'.date('Y').'-'.strtoupper(bin2hex(random_bytes(4))); }
function return_url(string $url,string $fallback='dashboard.php'):string { return preg_match('/^(?:[a-z0-9_-]+\.php|\/public\/)[a-z0-9_?=&.#%+\/-]*$/i',$url)?$url:$fallback; }
