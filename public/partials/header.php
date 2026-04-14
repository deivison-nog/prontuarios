<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Prontuários') ?> — Sistema de Prontuários</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar d-flex flex-column">
        <div class="sidebar-brand">
            <span class="brand-icon"><i class="bi bi-clipboard2-pulse-fill"></i></span>
            <span class="brand-text">Prontuários</span>
        </div>

        <ul class="nav flex-column sidebar-nav mt-2">
            <li class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'novo' ? 'active' : '' ?>" href="novo.php">
                    <i class="bi bi-plus-circle-fill"></i> Novo Prontuário
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'listar' ? 'active' : '' ?>" href="listar.php">
                    <i class="bi bi-list-ul"></i> Listar / Buscar
                </a>
            </li>
        </ul>

        <div class="mt-auto sidebar-footer">
            <div class="user-info">
                <span class="user-avatar"><i class="bi bi-person-circle"></i></span>
                <div>
                    <div class="user-name"><?= h($user['nome'] ?? '') ?></div>
                    <div class="user-role badge-role"><?= h(ucfirst($user['perfil'] ?? '')) ?></div>
                </div>
            </div>
            <a href="logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </div>
    </nav>

    <!-- Page content -->
    <div id="page-content" class="flex-grow-1">
        <!-- Top bar -->
        <header class="topbar d-flex align-items-center">
            <button id="sidebarToggle" class="btn btn-light btn-sm me-3">
                <i class="bi bi-list fs-5"></i>
            </button>
            <nav aria-label="breadcrumb" class="me-auto">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Início</a></li>
                    <?php if (!empty($breadcrumb)): ?>
                        <?php foreach ($breadcrumb as $bc): ?>
                            <?php if (!empty($bc['url'])): ?>
                                <li class="breadcrumb-item"><a href="<?= h($bc['url']) ?>"><?= h($bc['label']) ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?= h($bc['label']) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </nav>
        </header>

        <main class="content-area">
