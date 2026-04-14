<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/models/Prontuario.php';

requireLogin('login.php');

$user  = usuarioAtual();
$model = new Prontuario($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$prontuario = $model->buscarPorId($id);

if (!$prontuario) {
    redirecionarComMensagem('listar.php', 'erro', 'Prontuário não encontrado.');
}

$model->excluir($id);
redirecionarComMensagem('listar.php', 'sucesso', "Prontuário excluído com sucesso.");
