<?php
session_start();
$pdo = new PDO(
    'mysql:host=localhost;
    dbname=purugflow;
    charset=utf8mb4',
    'root','',
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function e($s){
    return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');
    }

    function exigir_login(){
    if(empty($_SESSION['uid'])){header('Location: login.php');exit;}
    }

function utilizador() {
    global $pdo;

    if (empty($_SESSION['uid'])) {
        return null;
    }

    $s = $pdo->prepare(
        'SELECT id, nome, email FROM users WHERE id = ? LIMIT 1'
    );

    $s->execute([$_SESSION['uid']]);

    $u = $s->fetch(PDO::FETCH_ASSOC);

    return $u !== false ? $u : null;
}

// Devolve a sala se o utilizador for dono ou membro; adiciona 'e_dono'
function acesso_sala($id){
    global $pdo;
    $s=$pdo->prepare('SELECT s.*, (s.dono_id=:u) e_dono FROM salas s WHERE s.id=:s AND (s.dono_id=:u OR EXISTS(SELECT 1 FROM sala_membros m WHERE m.sala_id=s.id AND m.user_id=:u))');
    $s->execute([':u'=>$_SESSION['uid'],':s'=>$id]);
    return $s->fetch();
}
