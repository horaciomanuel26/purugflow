-- Opcional: copia os dados da base HoGrupo para a purugflow (importe purugflow.sql primeiro).
USE purugflow;
INSERT INTO utilizadores SELECT * FROM HoGrupo.utilizadores;
INSERT INTO projetos SELECT * FROM HoGrupo.projetos;
INSERT INTO membros SELECT * FROM HoGrupo.membros;
INSERT INTO tarefas SELECT * FROM HoGrupo.tarefas;
