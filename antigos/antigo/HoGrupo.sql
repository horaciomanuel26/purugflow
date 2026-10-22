CREATE DATABASE IF NOT EXISTS HoGrupo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE HoGrupo;
CREATE TABLE utilizadores(
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  cor CHAR(7) NOT NULL DEFAULT '#2f5bea',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE projetos(
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  dono_id INT NOT NULL,
  rev INT NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(dono_id) REFERENCES utilizadores(id) ON DELETE CASCADE
);
CREATE TABLE membros(
  projeto_id INT NOT NULL,
  utilizador_id INT NOT NULL,
  PRIMARY KEY(projeto_id,utilizador_id),
  FOREIGN KEY(projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
  FOREIGN KEY(utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
);
CREATE TABLE tarefas(
  id INT AUTO_INCREMENT PRIMARY KEY,
  projeto_id INT NOT NULL,
  titulo VARCHAR(120) NOT NULL,
  descricao TEXT,
  responsavel_id INT NULL,
  estado ENUM('Tarefas','Em andamento','Feitas','Canceladas') NOT NULL DEFAULT 'Tarefas',
  cor CHAR(7) NOT NULL DEFAULT '#8a94a6',
  dia DATE NOT NULL,
  versao INT NOT NULL DEFAULT 1,
  INDEX(projeto_id,dia),
  FOREIGN KEY(projeto_id) REFERENCES projetos(id) ON DELETE CASCADE,
  FOREIGN KEY(responsavel_id) REFERENCES utilizadores(id) ON DELETE SET NULL
);
