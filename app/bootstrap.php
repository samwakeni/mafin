<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Africa/Johannesburg');
function envLoad(string $file): void {
 if (!is_file($file)) return;
 foreach (file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line) {
  $line=trim($line); if ($line==='' || $line[0]==='#' || !str_contains($line,'=')) continue;
  [$k,$v]=explode('=',$line,2); $_ENV[trim($k)]=trim(trim($v),"'\\");
 }
}
envLoad(dirname(__DIR__).'/.env');
function db(): PDO {
 static $pdo;
 if ($pdo instanceof PDO) return $pdo;
 $dsn='mysql:host='.($_ENV['DB_HOST']??'127.0.0.1').';port='.($_ENV['DB_PORT']??'3306').';dbname='.($_ENV['DB_DATABASE']??'personal_finance').';charset=utf8mb4';
 return $pdo=new PDO($dsn,$_ENV['DB_USERNAME']??'root',$_ENV['DB_PASSWORD']??'',[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false
 ]);
}
function e($v): string { return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verifyCsrf(): void { if(!hash_equals(csrf(),(string)($_POST['csrf_token']??''))){http_response_code(419);exit('Session expired. Refresh and try again.');} }
function auth(): void { if(empty($_SESSION['uid'])){header('Location: ?page=login');exit;} }
function entity(): array {
 $s=db()->prepare('SELECT id,name,entity_type FROM financial_entities WHERE user_id=? AND is_active=1 ORDER BY id');
 $s->execute([$_SESSION['uid']]);$all=$s->fetchAll();
 if(!$all) throw new RuntimeException('No financial entities found.');
 foreach($all as $row) if((int)$row['id']===(int)($_SESSION['entity_id']??0)) return $row;
 $_SESSION['entity_id']=(int)$all[0]['id'];return $all[0];
}
function jsonOut(array $data,int $status=200): never { http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode($data);exit; }
