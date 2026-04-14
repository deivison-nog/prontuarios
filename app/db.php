<?php
// ============================================================
// Conexão PDO — inclua este arquivo onde precisar de $pdo
// ============================================================

require_once __DIR__ . '/../config/database.php';

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(503);
    die('<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></head><body class="d-flex align-items-center justify-content-center min-vh-100 bg-light"><div class="text-center"><h1 class="display-1 text-danger"><i class="bi bi-exclamation-triangle"></i></h1><h4>Não foi possível conectar ao banco de dados.</h4><p class="text-muted">Verifique as configurações em <code>config/database.php</code> e tente novamente.</p></div></body></html>');
}
