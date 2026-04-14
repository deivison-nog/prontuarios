<?php
// ============================================================
// Autenticação e controle de sessão
// ============================================================

function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false,   // mude para true em HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function autenticar(PDO $pdo, string $email, string $senha): array|false
{
    $stmt = $pdo->prepare(
        'SELECT id, nome, email, perfil FROM usuarios WHERE email = ? AND ativo = 1'
    );
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        return false;
    }

    // Buscar senha separadamente para não expô-la no array
    $stmtSenha = $pdo->prepare('SELECT senha FROM usuarios WHERE id = ?');
    $stmtSenha->execute([$usuario['id']]);
    $hash = $stmtSenha->fetchColumn();

    if (!password_verify($senha, $hash)) {
        return false;
    }

    return $usuario;
}

function logarUsuario(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']    = $usuario['id'];
    $_SESSION['user_nome']  = $usuario['nome'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['user_perfil']= $usuario['perfil'];
}

function deslogarUsuario(): void
{
    $_SESSION = [];
    session_destroy();
}

function usuarioLogado(): bool
{
    return !empty($_SESSION['user_id']);
}

function requireLogin(string $redirect = 'login.php'): void
{
    iniciarSessao();
    if (!usuarioLogado()) {
        header("Location: $redirect");
        exit;
    }
}

function usuarioAtual(): array
{
    return [
        'id'     => $_SESSION['user_id']    ?? 0,
        'nome'   => $_SESSION['user_nome']  ?? '',
        'email'  => $_SESSION['user_email'] ?? '',
        'perfil' => $_SESSION['user_perfil']?? '',
    ];
}
