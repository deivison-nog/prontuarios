<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/models/Prontuario.php';

requireLogin('login.php');

$user  = usuarioAtual();
$model = new Prontuario($pdo);

// Parâmetros de busca e paginação
$busca  = trim($_GET['q']      ?? '');
$status = trim($_GET['status'] ?? '');
$porPag = 20;
$pagina = max(1, (int)($_GET['p'] ?? 1));
$offset = ($pagina - 1) * $porPag;

$total    = $model->contarTodos($busca);
$registros = $model->listar($busca, $porPag, $offset);
$totalPag = (int)ceil($total / $porPag);

$pageTitle  = 'Listar Prontuários';
$activeMenu = 'listar';
$breadcrumb = [['label' => 'Listar / Buscar']];

include 'partials/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="page-title mb-0"><i class="bi bi-list-ul text-primary me-2"></i>Prontuários</h2>
    <a href="novo.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Novo
    </a>
</div>

<?php include 'partials/flash.php'; ?>

<!-- Formulário de busca -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-8">
                <label class="form-label small text-muted">Buscar por nome, número ou município</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="q" value="<?= h($busca) ?>"
                        placeholder="Digite para buscar..." autofocus>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
            <?php if ($busca): ?>
                <div class="col-12 col-md-2">
                    <a href="listar.php" class="btn btn-outline-secondary w-100">Limpar</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Resultado -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <?php if ($busca): ?>
                Resultados para <strong>"<?= h($busca) ?>"</strong> — <?= $total ?> encontrado(s)
            <?php else: ?>
                Total de registros: <strong><?= $total ?></strong>
            <?php endif; ?>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($registros)): ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <?= $busca ? 'Nenhum resultado encontrado.' : 'Nenhum prontuário cadastrado ainda.' ?>
                <br><a href="novo.php" class="btn btn-primary btn-sm mt-3">Criar prontuário</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nº Prontuário</th>
                            <th>Paciente</th>
                            <th>Data Nasc.</th>
                            <th>Município</th>
                            <th>Digitado por</th>
                            <th>Status</th>
                            <th>Data Cadastro</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registros as $p): ?>
                            <tr>
                                <td class="text-muted small"><?= $p['id'] ?></td>
                                <td class="fw-semibold text-primary"><?= h($p['numero_prontuario'] ?: '—') ?></td>
                                <td><?= h($p['nome']) ?></td>
                                <td><?= h($p['data_nascimento'] ? formatarData($p['data_nascimento']) : '—') ?></td>
                                <td><?= h($p['municipio'] ?: '—') ?></td>
                                <td class="text-muted small"><?= h($p['digitador']) ?></td>
                                <td><?php
                                    $badge = match($p['status']) {
                                        'revisado' => ['bg-success', 'Revisado'],
                                        'digitado' => ['bg-primary', 'Digitado'],
                                        default    => ['bg-secondary', 'Pendente'],
                                    };
                                ?><span class="badge <?= $badge[0] ?>"><?= $badge[1] ?></span></td>
                                <td class="text-muted small"><?= formatarData($p['created_at']) ?></td>
                                <td class="text-end">
                                    <a href="visualizar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        title="Excluir"
                                        data-id="<?= $p['id'] ?>"
                                        data-nome="<?= h($p['nome']) ?>"
                                        onclick="confirmarExclusao(this.dataset.id, this.dataset.nome)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPag > 1): ?>
                <div class="d-flex justify-content-center py-3">
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($i = 1; $i <= $totalPag; $i++): ?>
                                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="?q=<?= urlencode($busca) ?>&p=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="bi bi-trash me-2"></i>Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Deseja excluir o prontuário de <strong id="nomeExcluir"></strong>?
                <br><small class="text-muted">Esta ação não pode ser desfeita.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" action="excluir.php" id="formExcluir">
                    <input type="hidden" name="id" id="idExcluir">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarExclusao(id, nome) {
    document.getElementById('idExcluir').value   = id;
    document.getElementById('nomeExcluir').textContent = nome;
    new bootstrap.Modal(document.getElementById('modalExcluir')).show();
}
</script>

<?php include 'partials/footer.php'; ?>
