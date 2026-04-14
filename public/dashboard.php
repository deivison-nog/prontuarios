<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/models/Prontuario.php';

requireLogin('login.php');

$user       = usuarioAtual();
$model      = new Prontuario($pdo);
$stats      = $model->estatisticas();
$recentes   = $model->recentes(8);

$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';
$breadcrumb = [];

include 'partials/header.php';
?>

<?php include 'partials/flash.php'; ?>

<!-- Título da página -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="page-title mb-1">Olá, <?= h(explode(' ', $user['nome'])[0]) ?>! 👋</h2>
        <p class="text-muted mb-0">Bem-vindo ao sistema de digitação de prontuários.</p>
    </div>
    <a href="novo.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Novo Prontuário
    </a>
</div>

<!-- Cards de estatísticas -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card stat-card--blue">
            <div class="stat-icon"><i class="bi bi-clipboard2-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total de Prontuários</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card stat-card--green">
            <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($stats['hoje']) ?></div>
                <div class="stat-label">Digitados Hoje</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card stat-card--orange">
            <div class="stat-icon"><i class="bi bi-calendar-week-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($stats['semana']) ?></div>
                <div class="stat-label">Última Semana</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card stat-card--purple">
            <div class="stat-icon"><i class="bi bi-patch-check-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($stats['revisados']) ?></div>
                <div class="stat-label">Revisados</div>
            </div>
        </div>
    </div>
</div>

<!-- Ações rápidas + Registros recentes -->
<div class="row g-4">
    <!-- Ações rápidas -->
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-lightning-fill text-warning me-2"></i>Ações Rápidas
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <a href="novo.php" class="quick-action-btn">
                    <span class="qa-icon bg-primary-subtle"><i class="bi bi-plus-circle-fill text-primary"></i></span>
                    <div>
                        <div class="fw-semibold">Novo Prontuário</div>
                        <div class="small text-muted">Iniciar nova ficha de digitação</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </a>
                <a href="listar.php" class="quick-action-btn">
                    <span class="qa-icon bg-success-subtle"><i class="bi bi-search text-success"></i></span>
                    <div>
                        <div class="fw-semibold">Buscar Prontuário</div>
                        <div class="small text-muted">Pesquisar por nome ou número</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </a>
                <a href="listar.php?status=digitado" class="quick-action-btn">
                    <span class="qa-icon bg-warning-subtle"><i class="bi bi-clock-fill text-warning"></i></span>
                    <div>
                        <div class="fw-semibold">Pendentes de Revisão</div>
                        <div class="small text-muted">Prontuários aguardando revisão</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Registros recentes -->
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Registros Recentes</span>
                <a href="listar.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentes)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Nenhum prontuário digitado ainda.
                        <br><a href="novo.php" class="btn btn-primary btn-sm mt-3">Criar primeiro prontuário</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nº Prontuário</th>
                                    <th>Paciente</th>
                                    <th>Município</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentes as $p): ?>
                                    <tr>
                                        <td class="fw-semibold text-primary"><?= h($p['numero_prontuario'] ?: '—') ?></td>
                                        <td><?= h($p['nome']) ?></td>
                                        <td><?= h($p['municipio'] ?: '—') ?></td>
                                        <td><?php
                                            $badge = match($p['status']) {
                                                'revisado' => ['bg-success', 'Revisado'],
                                                'digitado' => ['bg-primary', 'Digitado'],
                                                default    => ['bg-secondary', 'Pendente'],
                                            };
                                        ?>
                                        <span class="badge <?= $badge[0] ?>"><?= $badge[1] ?></span>
                                        </td>
                                        <td class="text-muted small"><?= formatarData($p['created_at']) ?></td>
                                        <td>
                                            <a href="visualizar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
