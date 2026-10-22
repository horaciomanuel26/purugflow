<?php require 'config.php'; exigir_login();
$sala=acesso_sala((int)($_GET['id']??0)); if(!$sala){header('Location: index.php');exit;}
$titulo=$sala['nome']; $com_burger=true; include 'includes/header.php'; include 'includes/topo.php'; ?>
<div class="layout" id="app" data-sala="<?=$sala['id']?>" data-view="dashboard">
<aside class="lado" id="lado">
  <h3><?=e($sala['nome'])?></h3>
  <nav><button data-go="dashboard" class="nav on">▦ Dashboard</button><button data-go="tarefas" class="nav">☰ Tarefas</button></nav>
  <label class="mut">Mês</label><input type="month" id="mes">
  <label class="mut">Utilizadores</label><div id="users"></div>
  <form id="addUser" class="linha" hidden><input type="email" name="email" placeholder="Email do utilizador" required><button class="btn">+</button></form>
</aside>
<main class="cont">
  <section id="dash"><div class="graficos"><div class="card"><h4>Estados</h4><div id="gDonut"></div></div><div class="card"><h4>Por utilizador</h4><div id="gBarras"></div></div></div></section>
  <section id="prog" class="card"><h4>Progresso geral</h4><div class="barra"><i id="pg"></i></div><small id="pgt" class="mut"></small></section>
  <section id="tar"><div class="linha"><button class="btn" id="nova">+ Nova tarefa</button></div><div class="colunas" id="cols"></div></section>
</main></div>
<div class="modal" id="modal" hidden><div class="caixa" id="caixa"></div></div>
<script src="assets/js/sala.js"></script></body></html>
