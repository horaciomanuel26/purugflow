<?php
// Configuração da base de dados (XAMPP: utilizador root, sem senha, por defeito)
$pdo = new PDO(
  'mysql:host=localhost;dbname=purugflow;charset=utf8mb4',
  'root',   // utilizador
  '',       // senha
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
   PDO::ATTR_EMULATE_PREPARES => false]
);
