<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/models/Prontuario.php';
require_once __DIR__ . '/../app/models/Atendimento.php';

requireLogin('login.php');

$user            = usuarioAtual();
$model           = new Prontuario($pdo);
$modelAtendimento = new Atendimento($pdo);
$erros  = [];
$dados  = [];

$camposProntuario = [
    'numero_prontuario','nome','sexo','estado_civil','profissao',
    'nome_pai','nome_mae','municipio','endereco','cliente','beneficiario',
    'causa_obito','status',
];
$camposDataProntuario = ['data_nascimento','data_obito'];
$camposAtendimento    = [
    'data_atendimento','programa','grupo_alvo','atividade','servico',
    'idade','diagnostico','prescricao','tratamento','evolucao','observacoes',
];

// Atendimentos para repopular o formulário em caso de erro
$atendimentos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Sanitizar dados do prontuário ---
    $dados = sanitizarPost(array_merge($camposProntuario, $camposDataProntuario));
    $dados['obito'] = isset($_POST['obito']) ? 1 : 0;

    // --- Sanitizar lista de atendimentos ---
    $atendimentosPost = $_POST['atendimentos'] ?? [];
    if (!is_array($atendimentosPost)) {
        $atendimentosPost = [];
    }
    foreach ($atendimentosPost as $at) {
        $bloco = [];
        foreach ($camposAtendimento as $campo) {
            $bloco[$campo] = trim($at[$campo] ?? '');
        }
        $atendimentos[] = $bloco;
    }
    // Garantir pelo menos um bloco de atendimento
    if (empty($atendimentos)) {
        $atendimentos[] = array_fill_keys($camposAtendimento, '');
    }

    // --- Validação ---
    if (empty($dados['nome'])) {
        $erros[] = 'O campo <strong>Nome do Paciente</strong> é obrigatório.';
    }
    foreach ($camposDataProntuario as $campo) {
        if (!empty($dados[$campo]) && !validarData($dados[$campo])) {
            $nomeCampo = match($campo) {
                'data_nascimento' => 'Data de Nascimento',
                'data_obito'      => 'Data do Óbito',
                default           => $campo,
            };
            $erros[] = "O campo <strong>{$nomeCampo}</strong> contém uma data inválida.";
        }
    }
    foreach ($atendimentos as $idx => $at) {
        $num = $idx + 1;
        if (!empty($at['data_atendimento']) && !validarData($at['data_atendimento'])) {
            $erros[] = "O campo <strong>Data de Atendimento</strong> do atendimento #{$num} contém uma data inválida.";
        }
    }

    if (empty($erros)) {
        $dados['usuario_id'] = $user['id'];
        $id = $model->inserirProntuario($dados);

        foreach ($atendimentos as $at) {
            // Salva o bloco apenas se tiver pelo menos um campo preenchido
            if (!empty(array_filter($at, fn($v) => $v !== ''))) {
                $modelAtendimento->inserir($id, $at);
            }
        }

        redirecionarComMensagem(
            "visualizar.php?id={$id}",
            'sucesso',
            'Prontuário digitado com sucesso!'
        );
    }
}

// Inicializar com um bloco vazio se não há atendimentos
if (empty($atendimentos)) {
    $atendimentos[] = array_fill_keys($camposAtendimento, '');
}

$pageTitle  = 'Novo Prontuário';
$activeMenu = 'novo';
$breadcrumb = [['label' => 'Novo Prontuário']];

include 'partials/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="page-title mb-0"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Novo Prontuário</h2>
</div>

<?php include 'partials/flash.php'; ?>

<?php if (!empty($erros)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Corrija os erros abaixo:</strong>
        <ul class="mb-0 mt-1">
            <?php foreach ($erros as $e): ?>
                <li><?= $e ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" novalidate id="formProntuario">

<!-- ============================================================ -->
<!-- SEÇÃO 1: DADOS DO PACIENTE                                   -->
<!-- ============================================================ -->
<div class="form-section mb-4">
    <div class="form-section-header">
        <i class="bi bi-person-fill"></i> Dados do Paciente
    </div>
    <div class="form-section-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="numero_prontuario">Número do Prontuário</label>
                <input type="text" class="form-control" id="numero_prontuario" name="numero_prontuario"
                    value="<?= h($dados['numero_prontuario'] ?? '') ?>" placeholder="Ex.: 001234">
            </div>

            <div class="col-md-8">
                <label class="form-label required" for="nome">Nome Completo do Paciente</label>
                <input type="text" class="form-control" id="nome" name="nome"
                    value="<?= h($dados['nome'] ?? '') ?>" placeholder="Nome completo" required>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="data_nascimento">Data de Nascimento</label>
                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                    value="<?= h($dados['data_nascimento'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="sexo">Sexo</label>
                <select class="form-select" id="sexo" name="sexo">
                    <option value="">Selecione...</option>
                    <option value="Masculino"  <?= ($dados['sexo'] ?? '') === 'Masculino'  ? 'selected' : '' ?>>Masculino</option>
                    <option value="Feminino"   <?= ($dados['sexo'] ?? '') === 'Feminino'   ? 'selected' : '' ?>>Feminino</option>
                    <option value="Outro"      <?= ($dados['sexo'] ?? '') === 'Outro'      ? 'selected' : '' ?>>Outro</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="estado_civil">Estado Civil</label>
                <select class="form-select" id="estado_civil" name="estado_civil">
                    <option value="">Selecione...</option>
                    <?php
                    $estadosCivis = ['Solteiro(a)','Casado(a)','Divorciado(a)','Viúvo(a)','União Estável','Separado(a)'];
                    foreach ($estadosCivis as $ec):
                    ?>
                        <option value="<?= h($ec) ?>" <?= ($dados['estado_civil'] ?? '') === $ec ? 'selected' : '' ?>>
                            <?= h($ec) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="profissao">Profissão</label>
                <input type="text" class="form-control" id="profissao" name="profissao"
                    value="<?= h($dados['profissao'] ?? '') ?>" placeholder="Ex.: Doméstica">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="nome_pai">Nome do Pai</label>
                <input type="text" class="form-control" id="nome_pai" name="nome_pai"
                    value="<?= h($dados['nome_pai'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="nome_mae">Nome da Mãe</label>
                <input type="text" class="form-control" id="nome_mae" name="nome_mae"
                    value="<?= h($dados['nome_mae'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="municipio">Município</label>
                <input type="text" class="form-control" id="municipio" name="municipio"
                    value="<?= h($dados['municipio'] ?? '') ?>" placeholder="Cidade">
            </div>

            <div class="col-md-8">
                <label class="form-label" for="endereco">Endereço</label>
                <input type="text" class="form-control" id="endereco" name="endereco"
                    value="<?= h($dados['endereco'] ?? '') ?>" placeholder="Rua, número, bairro">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="cliente">Cliente / Responsável</label>
                <input type="text" class="form-control" id="cliente" name="cliente"
                    value="<?= h($dados['cliente'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="beneficiario">Beneficiário</label>
                <input type="text" class="form-control" id="beneficiario" name="beneficiario"
                    value="<?= h($dados['beneficiario'] ?? '') ?>">
            </div>

            <!-- Óbito -->
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="obito" name="obito"
                        value="1" <?= !empty($dados['obito']) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="obito">Paciente falecido</label>
                </div>
            </div>

            <div class="col-md-3" id="bloco_obito" style="display: <?= !empty($dados['obito']) ? 'block' : 'none' ?>;">
                <label class="form-label" for="data_obito">Data do Óbito</label>
                <input type="date" class="form-control" id="data_obito" name="data_obito"
                    value="<?= h($dados['data_obito'] ?? '') ?>">
            </div>

            <div class="col-md-9" id="bloco_causa" style="display: <?= !empty($dados['obito']) ? 'block' : 'none' ?>;">
                <label class="form-label" for="causa_obito">Causa do Óbito</label>
                <input type="text" class="form-control" id="causa_obito" name="causa_obito"
                    value="<?= h($dados['causa_obito'] ?? '') ?>">
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- SEÇÃO 2: ATENDIMENTOS                                        -->
<!-- ============================================================ -->
<div class="form-section mb-4">
    <div class="form-section-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-heart-pulse-fill"></i> Atendimentos / Evoluções</span>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-adicionar-atendimento">
            <i class="bi bi-plus-circle me-1"></i>Adicionar Atendimento
        </button>
    </div>
    <div class="form-section-body">
        <div id="atendimentos-container">
            <?php foreach ($atendimentos as $idx => $at): ?>
                <?php include __DIR__ . '/partials/atendimento_bloco.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- SEÇÃO 3: CONTROLE                                            -->
<!-- ============================================================ -->
<div class="form-section mb-4">
    <div class="form-section-header">
        <i class="bi bi-gear-fill"></i> Controle
    </div>
    <div class="form-section-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="status">Status do Prontuário</label>
                <select class="form-select" id="status" name="status">
                    <option value="digitado"  <?= ($dados['status'] ?? 'digitado') === 'digitado'  ? 'selected' : '' ?>>Digitado</option>
                    <option value="revisado"  <?= ($dados['status'] ?? '') === 'revisado'  ? 'selected' : '' ?>>Revisado</option>
                    <option value="pendente"  <?= ($dados['status'] ?? '') === 'pendente'  ? 'selected' : '' ?>>Pendente</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Botões -->
<div class="d-flex gap-2 justify-content-end mb-4">
    <a href="dashboard.php" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i>Cancelar
    </a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-floppy-fill me-2"></i>Salvar Prontuário
    </button>
</div>

</form>

<!-- Template oculto para novos blocos de atendimento -->
<template id="atendimento-template">
    <?php
    $idx = '__INDEX__';
    $at  = array_fill_keys($camposAtendimento, '');
    include __DIR__ . '/partials/atendimento_bloco.php';
    ?>
</template>

<script>
// Toggle óbito
document.getElementById('obito').addEventListener('change', function () {
    const show = this.checked;
    document.getElementById('bloco_obito').style.display = show ? 'block' : 'none';
    document.getElementById('bloco_causa').style.display = show ? 'block' : 'none';
});

// Contador para índices únicos dos novos blocos
let nextAtendimentoIndex = <?= count($atendimentos) ?>;

function atualizarBotoesRemover() {
    const blocos = document.querySelectorAll('#atendimentos-container .atendimento-bloco');
    blocos.forEach(function (bloco) {
        const btn = bloco.querySelector('.btn-remover-atendimento');
        if (btn) {
            btn.style.display = blocos.length > 1 ? 'inline-flex' : 'none';
        }
    });
}

function atualizarNumeros() {
    const blocos = document.querySelectorAll('#atendimentos-container .atendimento-bloco');
    blocos.forEach(function (bloco, i) {
        const span = bloco.querySelector('.numero-atendimento');
        if (span) span.textContent = '#' + (i + 1);
    });
}

document.getElementById('btn-adicionar-atendimento').addEventListener('click', function () {
    const template = document.getElementById('atendimento-template');
    const html     = template.innerHTML.replaceAll('__INDEX__', nextAtendimentoIndex++);
    const wrapper  = document.createElement('div');
    wrapper.innerHTML = html;
    const bloco = wrapper.firstElementChild;
    document.getElementById('atendimentos-container').appendChild(bloco);

    bloco.querySelector('.btn-remover-atendimento').addEventListener('click', removerAtendimento);

    atualizarBotoesRemover();
    atualizarNumeros();
});

document.querySelectorAll('.btn-remover-atendimento').forEach(function (btn) {
    btn.addEventListener('click', removerAtendimento);
});

function removerAtendimento() {
    this.closest('.atendimento-bloco').remove();
    atualizarBotoesRemover();
    atualizarNumeros();
}

atualizarBotoesRemover();
</script>

<?php include 'partials/footer.php'; ?>

