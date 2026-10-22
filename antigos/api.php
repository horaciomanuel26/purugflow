<?php require 'config.php'; header('Content-Type: application/json');
if(empty($_SESSION['uid'])){http_response_code(401);exit('{}');}
$a=$_REQUEST['a']??''; $uid=$_SESSION['uid'];
$sala=acesso_sala((int)($_REQUEST['sala']??0));
function tarefa_ok($id){ global $pdo,$sala; $s=$pdo->prepare('SELECT * FROM tarefas WHERE id=? AND sala_id=?'); $s->execute([$id,$sala['id']]); return $s->fetch(); }
if(!$sala){http_response_code(403);exit('{"erro":"Sem acesso"}');}
$estados=['pendente','andamento','feita','cancelada']; $out=['ok'=>1];
switch($a){
case 'listar':
  $mes=preg_match('/^\d{4}-\d{2}$/',$_GET['mes']??'')?$_GET['mes']:date('Y-m');
  $s=$pdo->prepare('SELECT t.*,u.nome resp FROM tarefas t LEFT JOIN users u ON u.id=t.responsavel_id WHERE t.sala_id=? AND DATE_FORMAT(t.data,"%Y-%m")=? ORDER BY t.data,t.id'); $s->execute([$sala['id'],$mes]); $t=$s->fetchAll();
  $sub=$pdo->prepare('SELECT * FROM subtarefas WHERE tarefa_id=? ORDER BY id');
  foreach($t as &$x){ $sub->execute([$x['id']]); $x['subs']=$sub->fetchAll(); } 
  $m=$pdo->prepare('SELECT u.id,u.nome,u.email FROM users u WHERE u.id=? OR u.id IN(SELECT user_id FROM sala_membros WHERE sala_id=?)'); $m->execute([$sala['dono_id'],$sala['id']]);
  $out+=['tarefas'=>$t,'membros'=>$m->fetchAll(),'dono'=>(int)$sala['dono_id'],'e_dono'=>(bool)$sala['e_dono'],'eu'=>$uid]; break;
case 'salvar':
  $d=$_POST; $est=in_array($d['estado'],$estados)?$d['estado']:'pendente'; $cor=preg_match('/^#[0-9a-f]{6}$/i',$d['cor'])?$d['cor']:'#111111'; $resp=(int)$d['responsavel_id']?:null;
  if($resp){$c=$pdo->prepare('SELECT 1 FROM users WHERE id=:r AND (id=:d OR id IN(SELECT user_id FROM sala_membros WHERE sala_id=:s))');$c->execute([':r'=>$resp,':d'=>$sala['dono_id'],':s'=>$sala['id']]); if(!$c->fetch())$resp=null;}
  if(!empty($d['id']) && tarefa_ok($d['id'])){ $pdo->prepare('UPDATE tarefas SET titulo=?,descricao=?,estado=?,cor=?,data=?,responsavel_id=? WHERE id=?')->execute([$d['titulo'],$d['descricao'],$est,$cor,$d['data'],$resp,$d['id']]); $id=$d['id']; }
  else { $pdo->prepare('INSERT INTO tarefas(sala_id,titulo,descricao,estado,cor,data,responsavel_id) VALUES(?,?,?,?,?,?,?)')->execute([$sala['id'],$d['titulo'],$d['descricao'],$est,$cor,$d['data'],$resp]); $id=$pdo->lastInsertId(); }
  $out['id']=$id; break;
case 'estado': if(($t=tarefa_ok($_POST['id'])) && in_array($_POST['estado'],$estados)) $pdo->prepare('UPDATE tarefas SET estado=? WHERE id=?')->execute([$_POST['estado'],$t['id']]); break;
case 'apagar': if($t=tarefa_ok($_POST['id'])) $pdo->prepare('DELETE FROM tarefas WHERE id=?')->execute([$t['id']]); break;
case 'sub_add': if(($t=tarefa_ok($_POST['id'])) && trim($_POST['titulo'])!=='') $pdo->prepare('INSERT INTO subtarefas(tarefa_id,titulo) VALUES(?,?)')->execute([$t['id'],trim($_POST['titulo'])]); break;
case 'sub_toggle': $pdo->prepare('UPDATE subtarefas s JOIN tarefas t ON t.id=s.tarefa_id SET s.feita=1-s.feita WHERE s.id=? AND t.sala_id=?')->execute([$_POST['sid'],$sala['id']]); break;
case 'sub_del': $pdo->prepare('DELETE s FROM subtarefas s JOIN tarefas t ON t.id=s.tarefa_id WHERE s.id=? AND t.sala_id=?')->execute([$_POST['sid'],$sala['id']]); break;
case 'membro_add': // SÓ O DONO
  if(!$sala['e_dono']){http_response_code(403);exit('{"erro":"Apenas o dono da sala pode adicionar utilizadores."}');}
  $s=$pdo->prepare('SELECT id FROM users WHERE email=?'); $s->execute([trim($_POST['email'])]); $u=$s->fetch();
  if(!$u){$out=['erro'=>'Nenhuma conta com esse email.'];break;}
  $pdo->prepare('INSERT IGNORE INTO sala_membros(sala_id,user_id) VALUES(?,?)')->execute([$sala['id'],$u['id']]); break;
case 'membro_del':
  if(!$sala['e_dono']){http_response_code(403);exit('{"erro":"Apenas o dono."}');}
  $pdo->prepare('DELETE FROM sala_membros WHERE sala_id=? AND user_id=?')->execute([$sala['id'],$_POST['uid']]); break;
}
echo json_encode($out);
