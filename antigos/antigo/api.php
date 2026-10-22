<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__.'/config.php';
function out($d,$c=200){http_response_code($c);echo json_encode($d,JSON_UNESCAPED_UNICODE);exit;}
set_exception_handler(fn($e)=>out(['erro'=>'Erro no servidor'],500));
function um($sql,$p=[]){global $pdo;$s=$pdo->prepare($sql);$s->execute($p);return $s;}
$a=$_GET['a']??'';
$in=json_decode(file_get_contents('php://input'),true)??[];
// só aceita JSON: impede pedidos forjados a partir de outros sites
if($_SERVER['REQUEST_METHOD']==='POST'&&stripos($_SERVER['CONTENT_TYPE']??'','application/json')!==0)out(['erro'=>'Pedido inválido'],400);
$uid=$_SESSION['uid']??0;
$ESTADOS=['Tarefas','Em andamento','Feitas','Canceladas'];

if($a==='registar'){
  $n=trim($in['nome']??'');$e=strtolower(trim($in['email']??''));$s=$in['senha']??'';
  if($n===''||!filter_var($e,FILTER_VALIDATE_EMAIL)||strlen($s)<6)out(['erro'=>'Preencha o nome, um email válido e uma senha com 6 ou mais caracteres'],422);
  if(um('SELECT 1 FROM utilizadores WHERE email=?',[$e])->fetch())out(['erro'=>'Este email já tem conta'],409);
  $cores=['#2f5bea','#e5484d','#f08a24','#30a46c','#8e4ec6','#d6409f','#12a594'];
  um('INSERT INTO utilizadores(nome,email,senha,cor) VALUES(?,?,?,?)',[$n,$e,password_hash($s,PASSWORD_DEFAULT),$cores[array_rand($cores)]]);
  session_regenerate_id(true);$_SESSION['uid']=(int)$pdo->lastInsertId();out(['ok'=>1]);
}
if($a==='entrar'){
  $u=um('SELECT id,senha FROM utilizadores WHERE email=?',[strtolower(trim($in['email']??''))])->fetch();
  if(!$u||!password_verify($in['senha']??'',$u['senha']))out(['erro'=>'Email ou senha incorretos'],401);
  session_regenerate_id(true);$_SESSION['uid']=(int)$u['id'];out(['ok'=>1]);
}
if($a==='sair'){session_destroy();out(['ok'=>1]);}
if($a==='eu')out($uid?um('SELECT id,nome,email,cor FROM utilizadores WHERE id=?',[$uid])->fetch():null);
if(!$uid)out(['erro'=>'Sessão expirada'],401);

function bump($p){um('UPDATE projetos SET rev=rev+1 WHERE id=?',[$p]);}
function membro($p){global $uid;if(!um('SELECT 1 FROM membros WHERE projeto_id=? AND utilizador_id=?',[$p,$uid])->fetch())out(['erro'=>'Sem acesso a este projeto'],403);}
function dono($p){global $uid;if(!um('SELECT 1 FROM projetos WHERE id=? AND dono_id=?',[$p,$uid])->fetch())out(['erro'=>'Só o dono do projeto pode fazer isto'],403);}

if($a==='projetos'){
  $r=['meus'=>[],'participo'=>[]];
  $q=um("SELECT p.id,p.nome,p.dono_id,u.nome dono,
    (SELECT COUNT(*) FROM tarefas t WHERE t.projeto_id=p.id) total,
    (SELECT COUNT(*) FROM tarefas t WHERE t.projeto_id=p.id AND t.estado='Feitas') feitas,
    (SELECT COUNT(*) FROM membros x WHERE x.projeto_id=p.id) membros
    FROM projetos p JOIN membros m ON m.projeto_id=p.id AND m.utilizador_id=?
    JOIN utilizadores u ON u.id=p.dono_id ORDER BY p.nome",[$uid]);
  foreach($q as $x)$r[$x['dono_id']==$uid?'meus':'participo'][]=$x;
  out($r);
}
if($a==='projeto_criar'){
  $n=trim($in['nome']??'');if($n==='')out(['erro'=>'Dê um nome ao projeto'],422);
  um('INSERT INTO projetos(nome,dono_id) VALUES(?,?)',[$n,$uid]);$id=(int)$pdo->lastInsertId();
  um('INSERT INTO membros VALUES(?,?)',[$id,$uid]);out(['id'=>$id]);
}

$p=(int)($_GET['p']??$in['p']??0);
membro($p);

if($a==='rev')out(['rev'=>(int)um('SELECT rev FROM projetos WHERE id=?',[$p])->fetchColumn()]);
if($a==='dados'){
  $proj=um('SELECT id,nome,dono_id,rev FROM projetos WHERE id=?',[$p])->fetch();
  $mem=um('SELECT u.id,u.nome,u.cor FROM membros m JOIN utilizadores u ON u.id=m.utilizador_id WHERE m.projeto_id=? ORDER BY u.nome',[$p])->fetchAll();
  $res=um("SELECT DATE_FORMAT(dia,'%Y-%m') m,responsavel_id r,estado e,COUNT(*) n FROM tarefas WHERE projeto_id=? GROUP BY m,r,e",[$p])->fetchAll();
  $t=[];$mes=$_GET['mes']??'';
  if(preg_match('/^\d{4}-\d{2}$/',$mes))
    $t=um("SELECT id,titulo,descricao,responsavel_id r,estado,cor,dia,versao FROM tarefas WHERE projeto_id=? AND dia>=? AND dia<=LAST_DAY(?) ORDER BY dia,id",[$p,$mes.'-01',$mes.'-01'])->fetchAll();
  out(['projeto'=>$proj,'membros'=>$mem,'resumo'=>$res,'tarefas'=>$t]);
}
if($a==='tarefa_salvar'){
  $tit=trim($in['titulo']??'');if($tit==='')out(['erro'=>'O título é obrigatório'],422);
  $est=in_array($in['estado']??'',$ESTADOS)?$in['estado']:'Tarefas';
  $cor=preg_match('/^#[0-9a-fA-F]{6}$/',$in['cor']??'')?$in['cor']:'#8a94a6';
  $dia=preg_match('/^\d{4}-\d{2}-\d{2}$/',$in['dia']??'')?$in['dia']:date('Y-m-d');
  $r=(int)($in['r']??0)?:null;
  if($r&&!um('SELECT 1 FROM membros WHERE projeto_id=? AND utilizador_id=?',[$p,$r])->fetch())$r=null;
  $desc=trim($in['descricao']??'');
  if(!empty($in['id'])){
    // proteção contra acidentes: só grava se ninguém alterou a tarefa entretanto
    $s=um('UPDATE tarefas SET titulo=?,descricao=?,responsavel_id=?,estado=?,cor=?,dia=?,versao=versao+1 WHERE id=? AND projeto_id=? AND versao=?',
      [$tit,$desc,$r,$est,$cor,$dia,(int)$in['id'],$p,(int)($in['versao']??0)]);
    if(!$s->rowCount())out(['erro'=>'Esta tarefa foi alterada por outra pessoa. A lista foi atualizada, tente de novo.'],409);
  }else um('INSERT INTO tarefas(projeto_id,titulo,descricao,responsavel_id,estado,cor,dia) VALUES(?,?,?,?,?,?,?)',[$p,$tit,$desc,$r,$est,$cor,$dia]);
  bump($p);out(['ok'=>1]);
}
if($a==='tarefa_apagar'){um('DELETE FROM tarefas WHERE id=? AND projeto_id=?',[(int)($in['id']??0),$p]);bump($p);out(['ok'=>1]);}
if($a==='membro_add'){
  dono($p);
  $u=um('SELECT id FROM utilizadores WHERE email=?',[strtolower(trim($in['email']??''))])->fetch();
  if(!$u)out(['erro'=>'Não existe conta com esse email. Peça à pessoa para criar conta primeiro.'],404);
  um('INSERT IGNORE INTO membros VALUES(?,?)',[$p,$u['id']]);bump($p);out(['ok'=>1]);
}
if($a==='membro_rem'){
  dono($p);$m=(int)($in['u']??0);
  if(um('SELECT 1 FROM projetos WHERE id=? AND dono_id=?',[$p,$m])->fetch())out(['erro'=>'O dono não pode ser removido'],422);
  um('DELETE FROM membros WHERE projeto_id=? AND utilizador_id=?',[$p,$m]);
  um('UPDATE tarefas SET responsavel_id=NULL WHERE projeto_id=? AND responsavel_id=?',[$p,$m]);bump($p);out(['ok'=>1]);
}
out(['erro'=>'Ação desconhecida'],404);
