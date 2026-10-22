<?php require 'config.php'; $erro='';
if($_SERVER['REQUEST_METHOD']==='POST'){ $n=trim($_POST['nome']); $m=trim($_POST['email']); $p=$_POST['senha'];
 if(strlen($p)<6) $erro='A senha deve ter pelo menos 6 caracteres.';
 else try{ $pdo->prepare('INSERT INTO users(nome,email,senha) VALUES(?,?,?)')->execute([$n,$m,password_hash($p,PASSWORD_DEFAULT)]); $_SESSION['uid']=$pdo->lastInsertId(); header('Location: index.php'); exit; }catch(PDOException $x){ $erro='Esse email já está registado.'; } }
$titulo='Registar'; $corpo='auth'; include 'includes/header.php'; ?>
<form class="auth-card" method="post"><h1>Criar conta</h1><?php if($erro):?><p class="erro"><?=e($erro)?></p><?php endif;?>
<input name="nome" placeholder="Nome" required><input name="email" type="email" placeholder="Email" required><input name="senha" type="password" placeholder="Senha (mín. 6)" required>
<button class="btn">Registar</button><p class="mut">Já tem conta? <a href="login.php">Entrar</a></p></form></body></html>
