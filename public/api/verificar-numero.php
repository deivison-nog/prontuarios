<?php
// ============================================================
// API: verifica se numero_prontuario já está em uso
// GET/POST ?numero=XXX[&exclude_id=N]
// Retorna JSON: {"existe": true|false}
// ============================================================

require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/models/Prontuario.php';

requireLogin('../login.php');

header('Content-Type: application/json; charset=utf-8');

$numero    = trim($_GET['numero']     ?? $_POST['numero']     ?? '');
$excludeId = (int)($_GET['exclude_id'] ?? $_POST['exclude_id'] ?? 0);

if ($numero === '') {
    echo json_encode(['existe' => false]);
    exit;
}

$model = new Prontuario($pdo);
echo json_encode(['existe' => $model->numeroProntuarioExiste($numero, $excludeId)]);
