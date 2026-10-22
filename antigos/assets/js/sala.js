const app=document.getElementById('app'),SALA=app.dataset.sala,$=s=>document.querySelector(s);
const EST=[['pendente','Tarefas'],['andamento','Em andamento'],['feita','Feitas'],['cancelada','Canceladas']];
const COR={pendente:'#a1a1aa',andamento:'#18181b',feita:'#52525b',cancelada:'#d4d4d8'};
let S={tarefas:[],membros:[],e_dono:false,dono:0,eu:0},filtro=0,aberta=null;
const mes=$('#mes');mes.value=new Date().toISOString().slice(0,7); // abre no mês atual
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
async function api(a,d={},m='POST'){const f=new URLSearchParams({a,sala:SALA,...d});const r=await fetch(m==='GET'?'api.php?'+f:'api.php',m==='GET'?{}:{method:'POST',body:f});const j=await r.json();if(j.erro)alert(j.erro);return j}
async function carregar(){const j=await api('listar',{mes:mes.value},'GET');if(!j.ok)return;S=j;render();if(aberta&&!$('#modal').hidden&&!document.activeElement.closest('#caixa'))abrir(aberta,true)}
const lista=()=>S.tarefas.filter(t=>!filtro||t.responsavel_id==filtro);
const prog=t=>t.subs.length?Math.round(t.subs.filter(s=>+s.feita).length/t.subs.length*100):(t.estado==='feita'?100:0);
function render(){
 $('#addUser').hidden=!S.e_dono;
 $('#users').innerHTML=`<div class="u ${!filtro?'on':''}" data-f="0">Geral</div>`+S.membros.map(m=>`<div class="u ${filtro==m.id?'on':''}" data-f="${m.id}">${esc(m.nome)}${S.e_dono&&m.id!=S.dono?`<button data-rm="${m.id}" title="Remover">✕</button>`:''}</div>`).join('');
 const L=lista();
 $('#cols').innerHTML=EST.map(([k,n])=>`<div class="col"><h5>${n} · ${L.filter(t=>t.estado===k).length}</h5>`+L.filter(t=>t.estado===k).map(t=>{const p=prog(t);return `<div class="tc ${k}" data-id="${t.id}"><span class="tit" style="background:${esc(t.cor)}">${esc(t.titulo)}</span>${t.subs.length?`<div class="barra"><i style="width:${p}%"></i></div>`:''}<div class="meta"><span>${t.subs.length?p+'% · '+t.subs.filter(s=>+s.feita).length+'/'+t.subs.length:''}</span><span>${esc(t.resp||'')}</span></div></div>`}).join('')+'</div>').join('');
 const tot=L.length,ok=L.filter(t=>t.estado==='feita').length,pc=tot?Math.round(ok/tot*100):0;
 $('#pg').style.width=pc+'%';$('#pgt').textContent=`${ok} de ${tot} tarefas feitas (${pc}%)`;
 donut(L);barras(L);
}
function donut(L){const c=EST.map(([k])=>L.filter(t=>t.estado===k).length),T=c.reduce((a,b)=>a+b,0),R=50,C=2*Math.PI*R;let off=0;
 const arcs=T?c.map((v,i)=>{const len=v/T*C,s=`<circle r="${R}" cx="70" cy="70" fill="none" stroke="${COR[EST[i][0]]}" stroke-width="18" stroke-dasharray="${len} ${C-len}" stroke-dashoffset="${-off}" transform="rotate(-90 70 70)"/>`;off+=len;return s}).join(''):`<circle r="${R}" cx="70" cy="70" fill="none" stroke="#e4e4e7" stroke-width="18"/>`;
 $('#gDonut').innerHTML=`<div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap"><svg width="140" height="140">${arcs}<text x="70" y="76" text-anchor="middle" font-size="22" font-weight="700">${T}</text></svg><div>${EST.map(([k,n],i)=>`<div style="font-size:13px"><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:${COR[k]};margin-right:6px"></span>${n}: ${c[i]}</div>`).join('')}</div></div>`}
function barras(L){const us=filtro?S.membros.filter(m=>m.id==filtro):S.membros;if(!us.length){$('#gBarras').innerHTML='';return}
 const base=S.tarefas,dados=us.map(u=>({n:u.nome.split(' ')[0],v:EST.map(([k])=>base.filter(t=>t.responsavel_id==u.id&&t.estado===k).length)})),mx=Math.max(1,...dados.flatMap(d=>d.v)),W=Math.max(300,dados.length*90),H=140;
 $('#gBarras').innerHTML=`<div style="overflow-x:auto"><svg width="${W}" height="${H+24}">`+dados.map((d,i)=>d.v.map((v,j)=>{const h=v/mx*H,x=i*90+10+j*18;return `<rect x="${x}" y="${H-h}" width="14" height="${h}" rx="3" fill="${COR[EST[j][0]]}"><title>${EST[j][1]}: ${v}</title></rect>`}).join('')+`<text x="${i*90+44}" y="${H+16}" text-anchor="middle" font-size="12" fill="#71717a">${esc(d.n)}</text>`).join('')+`</svg></div>`}
function abrir(id,quiet){aberta=id;const t=S.tarefas.find(x=>x.id==id)||{id:'',titulo:'',descricao:'',estado:'pendente',cor:'#18181b',data:new Date().toISOString().slice(0,10),responsavel_id:'',subs:[]};
 $('#caixa').innerHTML=`<input id="f_t" value="${esc(t.titulo)}" placeholder="Título"><textarea id="f_d" rows="3" placeholder="Descrição">${esc(t.descricao)}</textarea>
 <div class="linha"><select id="f_e">${EST.map(([k,n])=>`<option value="${k}" ${t.estado===k?'selected':''}>${n}</option>`).join('')}</select><input type="color" id="f_c" value="${esc(t.cor)}" style="width:56px;padding:2px"><input type="date" id="f_dt" value="${t.data}"></div>
 <select id="f_r"><option value="">Sem responsável</option>${S.membros.map(m=>`<option value="${m.id}" ${t.responsavel_id==m.id?'selected':''}>${esc(m.nome)}</option>`).join('')}</select>
 ${t.id?`<div class="barra"><i style="width:${prog(t)}%"></i></div><div>${t.subs.map(s=>`<div class="sub ${+s.feita?'f':''}"><input type="checkbox" data-s="${s.id}" ${+s.feita?'checked':''}><span>${esc(s.titulo)}</span><button data-sd="${s.id}">✕</button></div>`).join('')}</div><div class="linha"><input id="f_s" placeholder="Nova subtarefa"><button class="btn ghost" id="addS">+</button></div>`:''}
 <div class="linha"><button class="btn" id="gravar">Guardar</button><button class="btn ghost" id="fechar">Fechar</button>${t.id?'<button class="btn ghost" id="apagar" style="margin-left:auto">Apagar</button>':''}</div>`;
 $('#modal').hidden=false}
const val=()=>({id:aberta||'',titulo:$('#f_t').value,descricao:$('#f_d').value,estado:$('#f_e').value,cor:$('#f_c').value,data:$('#f_dt').value,responsavel_id:$('#f_r').value});
document.addEventListener('click',async e=>{const g=e.target,q=s=>g.closest(s);let x;
 if(q('#burger'))$('#lado').classList.toggle('open');
 else if(x=q('[data-go]')){app.dataset.view=x.dataset.go;document.querySelectorAll('.nav').forEach(n=>n.classList.toggle('on',n===x));$('#lado').classList.remove('open')}
 else if(x=q('[data-rm]')){if(confirm('Remover utilizador da sala?')){await api('membro_del',{uid:x.dataset.rm});carregar()}}
 else if(x=q('[data-f]')){filtro=+x.dataset.f;render()}
 else if(q('#nova')){aberta=null;abrir(null)}
 else if(x=q('.tc')){abrir(+x.dataset.id)}
 else if(q('#fechar')||g.id==='modal'){$('#modal').hidden=true;aberta=null}
 else if(q('#gravar')){const r=await api('salvar',val());aberta=r.id;await carregar();$('#modal').hidden=true}
 else if(q('#apagar')){if(confirm('Apagar tarefa?')){await api('apagar',{id:aberta});$('#modal').hidden=true;carregar()}}
 else if(q('#addS')){const v=$('#f_s').value.trim();if(v){await api('sub_add',{id:aberta,titulo:v});await carregar();abrir(aberta)}}
 else if(x=q('[data-sd]')){await api('sub_del',{sid:x.dataset.sd});await carregar();abrir(aberta)}
 else if(g.matches('[data-s]')){await api('sub_toggle',{sid:g.dataset.s});await carregar();abrir(aberta)}});
$('#addUser').onsubmit=async e=>{e.preventDefault();const r=await api('membro_add',{email:e.target.email.value});if(r.ok){e.target.reset();carregar()}};
mes.onchange=carregar;carregar();setInterval(()=>{if($('#modal').hidden)carregar()},5000); // tempo real por polling
