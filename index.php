<?php
require_once __DIR__ . '/../app/auth.php';
iniciarSessao();
header('Location: ' . (usuarioLogado() ? 'public/dashboard.php' : 'public/login.php'));
exit;
