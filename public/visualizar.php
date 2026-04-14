<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/models/Prontuario.php';

requireLogin('login.php');

$user  = usuarioAtual();
$model = new Prontuario($pdo);

$id = (int)($_GET['id'] ?? 0);
$prontuario = $model->buscarPorId($id);

if (!$prontuario) {
    redirecionarComMensagem('listar.php', 'erro', 'Prontuário não encontrado.');
}

$pageTitle  = 'Visualizar Prontuário';
$activeMenu = 'listar';
$breadcrumb = [
    ['label' => 'Listar', 'url' => 'listar.php'],
    ['label' => 'Prontuário #' . $id],
];

include 'partials/header.php';
?>

<?php include 'partials/flash.php'; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="page-title mb-0">
        <i class="bi bi-clipboard2-pulse-fill text-primary me-2"></i>
        Prontuário <?= h($prontuario['numero_prontuario'] ? '#' . $prontuario['numero_prontuario'] : '#' . $prontuario['id']) ?>
    </h2>
    <div class="d-flex gap-2">
        <a href="editar.php?id=<?= $id ?>" class="btn btn-warning">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <a href="listar.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>
</div>

<!-- Badge status -->
<div class="mb-4">
    <?php
    $badge = match($prontuario['status']) {
        'revisado' => ['bg-success', 'Revisado'],
        'digitado' => ['bg-primary', 'Digitado'],
        default    => ['bg-secondary', 'Pendente'],
    };
    ?>
    <span class="badge <?= $badge[0] ?> fs-6 px-3 py-2"><?= $badge[1] ?></span>
    <span class="text-muted small ms-3">
        Digitado por <strong><?= h($prontuario['digitador']) ?></strong>
        em <?= formatarData($prontuario['created_at']) ?>
        <?php if ($prontuario['updated_at']): ?>
            · Atualizado em <?= formatarData($prontuario['updated_at']) ?>
        <?php endif; ?>
    </span>
</div>

<!-- SEÇÃO: DADOS DO PACIENTE -->
<div class="form-section mb-4">
    <div class="form-section-header">
        <i class="bi bi-person-fill"></i> Dados do Paciente
    </div>
    <div class="form-section-body">
        <div class="row g-3">
            <?php /* Patient data fields */ ?>
            <div class="col-md-4"><?php campoVisualizacao('Número do Prontuário', $prontuario['numero_prontuario']); ?></div>
            <div class="col-md-8"><?php campoVisualizacao('Nome Completo', $prontuario['nome']); ?></div>
            <div class="col-md-3"><?php campoVisualizacao('Data de Nascimento', $prontuario['data_nascimento'] ? formatarData($prontuario['data_nascimento']) : ''); ?></div>
            <div class="col-md-3"><?php campoVisualizacao('Sexo', $prontuario['sexo']); ?></div>
            <div class="col-md-3"><?php campoVisualizacao('Estado Civil', $prontuario['estado_civil']); ?></div>
            <div class="col-md-3"><?php campoVisualizacao('Profissão', $prontuario['profissao']); ?></div>
            <div class="col-md-6"><?php campoVisualizacao('Nome do Pai', $prontuario['nome_pai']); ?></div>
            <div class="col-md-6"><?php campoVisualizacao('Nome da Mãe', $prontuario['nome_mae']); ?></div>
            <div class="col-md-4"><?php campoVisualizacao('Município', $prontuario['municipio']); ?></div>
            <div class="col-md-8"><?php campoVisualizacao('Endereço', $prontuario['endereco']); ?></div>
            <div class="col-md-6"><?php campoVisualizacao('Cliente / Responsável', $prontuario['cliente']); ?></div>
            <div class="col-md-6"><?php campoVisualizacao('Beneficiário', $prontuario['beneficiario']); ?></div>
            <?php if ($prontuario['obito']): ?>
                <div class="col-md-3"><?php campoVisualizacao('Falecido', 'Sim'); ?></div>
                <div class="col-md-3"><?php campoVisualizacao('Data do Óbito', $prontuario['data_obito'] ? formatarData($prontuario['data_obito']) : ''); ?></div>
                <div class="col-md-6"><?php campoVisualizacao('Causa do Óbito', $prontuario['causa_obito']); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SEÇÃO: ATENDIMENTO -->
<div class="form-section mb-4">
    <div class="form-section-header">
        <i class="bi bi-heart-pulse-fill"></i> Atendimento / Evolução
    </div>
    <div class="form-section-body">
        <div class="row g-3">
            <div class="col-md-3"><?php campoVisualizacao('Data do Atendimento', $prontuario['data_atendimento'] ? formatarData($prontuario['data_atendimento']) : ''); ?></div>
            <div class="col-md-3"><?php campoVisualizacao('Idade', $prontuario['idade']); ?></div>
            <div class="col-md-3"><?php campoVisualizacao('Programa', $prontuario['programa']); ?></div>
            <div class="col-md-3"><?php campoVisualizacao('Grupo Alvo', $prontuario['grupo_alvo']); ?></div>
            <div class="col-md-4"><?php campoVisualizacao('Atividade', $prontuario['atividade']); ?></div>
            <div class="col-md-4"><?php campoVisualizacao('Serviço', $prontuario['servico']); ?></div>
            <div class="col-md-12"><?php campoVisualizacao('Diagnóstico', $prontuario['diagnostico']); ?></div>
            <div class="col-md-6"><?php campoVisualizacao('Prescrição', $prontuario['prescricao']); ?></div>
            <div class="col-md-6"><?php campoVisualizacao('Tratamento', $prontuario['tratamento']); ?></div>
            <div class="col-md-12"><?php campoVisualizacao('Evolução', $prontuario['evolucao']); ?></div>
            <div class="col-md-12"><?php campoVisualizacao('Observações', $prontuario['observacoes']); ?></div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
