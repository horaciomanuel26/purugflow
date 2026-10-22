<?php require 'config.php'; exigir_login();
if(isset($_POST['nova']) && trim($_POST['nome'])!==''){ $pdo->prepare('INSERT INTO salas(nome,dono_id) VALUES(?,?)')->execute([trim($_POST['nome']),$_SESSION['uid']]); header('Location: sala.php?id='.$pdo->lastInsertId()); exit; }
$meus=$pdo->prepare('SELECT * FROM salas WHERE dono_id=? ORDER BY id DESC'); $meus->execute([$_SESSION['uid']]);
$part=$pdo->prepare('SELECT s.*,u.nome dono FROM salas s JOIN sala_membros m ON m.sala_id=s.id JOIN users u ON u.id=s.dono_id WHERE m.user_id=? AND s.dono_id<>? ORDER BY s.id DESC'); $part->execute([$_SESSION['uid'],$_SESSION['uid']]);
$titulo='Salas'; include 'includes/header.php'; include 'includes/topo.php'; ?>
<main class="wrap">
<h2>Os meus projetos</h2>
<form method="post" class="linha"><input name="nome" placeholder="Nova sala (ex.: Outubro 2026)" required><button class="btn" name="nova">Criar sala</button></form>
<div class="grid"><?php foreach($meus as $s):?><a class="sala" href="sala.php?id=<?=$s['id']?>"><b><?=e($s['nome'])?></b><small>Dono</small></a><?php endforeach;?></div>
<h2>Projetos em que participo</h2>
<div class="grid"><?php foreach($part as $s):?><a class="sala" href="sala.php?id=<?=$s['id']?>"><b><?=e($s['nome'])?></b><small>de <?=e($s['dono'])?></small></a><?php endforeach;?></div>
</main></body></html>
