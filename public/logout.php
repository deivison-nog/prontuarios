<?php
require_once __DIR__ . '/../app/auth.php';
iniciarSessao();
deslogarUsuario();
header('Location: login.php');
exit;
