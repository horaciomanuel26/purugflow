<?php require 'config.php'; $erro='';
if($_SERVER['REQUEST_METHOD']==='POST'){ $s=$pdo->prepare('SELECT * FROM users WHERE email=?'); $s->execute([trim($_POST['email'])]); $u=$s->fetch();
 if($u && password_verify($_POST['senha'],$u['senha'])){ $_SESSION['uid']=$u['id']; header('Location: index.php'); exit; } $erro='Email ou senha incorretos.'; }
$titulo='Entrar'; $corpo='auth'; include 'includes/header.php'; ?>
<form class="auth-card" method="post"><h1>Entrar</h1><?php if($erro):?><p class="erro"><?=e($erro)?></p><?php endif;?>
<input name="email" type="email" placeholder="Email" required><input name="senha" type="password" placeholder="Senha" required>
<button class="btn">Entrar</button><p class="mut">Sem conta? <a href="registrar.php">Registar</a></p></form></body></html>
