<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/models/Prontuario.php';

requireLogin('login.php');

$user   = usuarioAtual();
$model  = new Prontuario($pdo);
$erros  = [];
$dados  = [];

$camposTexto = [
    'numero_prontuario','nome','sexo','estado_civil','profissao',
    'nome_pai','nome_mae','municipio','endereco','cliente','beneficiario',
    'causa_obito','programa','grupo_alvo','atividade','servico','idade',
    'diagnostico','prescricao','tratamento','evolucao','observacoes','status',
];
$camposData = ['data_nascimento','data_obito','data_atendimento'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = sanitizarPost(array_merge($camposTexto, $camposData));
    $dados['obito'] = isset($_POST['obito']) ? 1 : 0;

    // Validação
    if (empty($dados['nome'])) {
        $erros[] = 'O campo <strong>Nome do Paciente</strong> é obrigatório.';
    }
    foreach ($camposData as $campo) {
        if (!empty($dados[$campo]) && !validarData($dados[$campo])) {
            $nomeCampo = match($campo) {
                'data_nascimento'  => 'Data de Nascimento',
                'data_obito'       => 'Data do Óbito',
                'data_atendimento' => 'Data de Atendimento',
                default            => $campo,
            };
            $erros[] = "O campo <strong>{$nomeCampo}</strong> contém uma data inválida.";
        }
    }
    if (!empty($dados['numero_prontuario']) && $model->numeroProntuarioExiste($dados['numero_prontuario'])) {
        $erros[] = 'O <strong>Número do Prontuário</strong> <em>' . h($dados['numero_prontuario']) . '</em> já está em uso. Informe um número diferente.';
    }

    if (empty($erros)) {
        $dados['usuario_id'] = $user['id'];
        $id = $model->inserir($dados);
        redirecionarComMensagem(
            "visualizar.php?id={$id}",
            'sucesso',
            'Prontuário digitado com sucesso!'
        );
    }
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
                <div class="invalid-feedback" id="numero_prontuario_feedback">
                    Este número de prontuário já está em uso.
                </div>
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
<!-- SEÇÃO 2: ATENDIMENTO / EVOLUÇÃO                              -->
<!-- ============================================================ -->
<div class="form-section mb-4">
    <div class="form-section-header">
        <i class="bi bi-heart-pulse-fill"></i> Atendimento / Evolução
    </div>
    <div class="form-section-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="data_atendimento">Data do Atendimento</label>
                <input type="date" class="form-control" id="data_atendimento" name="data_atendimento"
                    value="<?= h($dados['data_atendimento'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="idade">Idade na Época</label>
                <input type="text" class="form-control" id="idade" name="idade"
                    value="<?= h($dados['idade'] ?? '') ?>" placeholder="Ex.: 45 anos">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="programa">Programa</label>
                <input type="text" class="form-control" id="programa" name="programa"
                    value="<?= h($dados['programa'] ?? '') ?>" placeholder="Ex.: ESF">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="grupo_alvo">Grupo Alvo</label>
                <input type="text" class="form-control" id="grupo_alvo" name="grupo_alvo"
                    value="<?= h($dados['grupo_alvo'] ?? '') ?>" placeholder="Ex.: Idoso">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="atividade">Atividade</label>
                <input type="text" class="form-control" id="atividade" name="atividade"
                    value="<?= h($dados['atividade'] ?? '') ?>" placeholder="Ex.: Consulta">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="servico">Serviço</label>
                <input type="text" class="form-control" id="servico" name="servico"
                    value="<?= h($dados['servico'] ?? '') ?>" placeholder="Ex.: Clínica Geral">
            </div>

            <div class="col-md-12">
                <label class="form-label" for="diagnostico">Diagnóstico</label>
                <textarea class="form-control" id="diagnostico" name="diagnostico" rows="3"
                    placeholder="CID, hipótese diagnóstica..."><?= h($dados['diagnostico'] ?? '') ?></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="prescricao">Prescrição</label>
                <textarea class="form-control" id="prescricao" name="prescricao" rows="3"
                    placeholder="Medicamentos prescritos..."><?= h($dados['prescricao'] ?? '') ?></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="tratamento">Tratamento</label>
                <textarea class="form-control" id="tratamento" name="tratamento" rows="3"
                    placeholder="Procedimentos, terapias..."><?= h($dados['tratamento'] ?? '') ?></textarea>
            </div>

            <div class="col-md-12">
                <label class="form-label" for="evolucao">Evolução</label>
                <textarea class="form-control" id="evolucao" name="evolucao" rows="4"
                    placeholder="Evolução clínica do paciente..."><?= h($dados['evolucao'] ?? '') ?></textarea>
            </div>

            <div class="col-md-12">
                <label class="form-label" for="observacoes">Observações</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                    placeholder="Informações adicionais..."><?= h($dados['observacoes'] ?? '') ?></textarea>
            </div>
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

<script>
// Toggle óbito
document.getElementById('obito').addEventListener('change', function () {
    const show = this.checked;
    document.getElementById('bloco_obito').style.display = show ? 'block' : 'none';
    document.getElementById('bloco_causa').style.display = show ? 'block' : 'none';
});

// Verificação em tempo real do número do prontuário
(function () {
    const input    = document.getElementById('numero_prontuario');
    const feedback = document.getElementById('numero_prontuario_feedback');
    if (!input) return;

    input.addEventListener('blur', function () {
        const numero = this.value.trim();
        if (numero === '') {
            input.classList.remove('is-invalid', 'is-valid');
            return;
        }

        fetch('api/verificar-numero.php?numero=' + encodeURIComponent(numero))
            .then(r => r.json())
            .then(data => {
                if (data.existe) {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                    if (feedback) feedback.textContent = 'O número "' + numero + '" já está em uso. Informe um número diferente.';
                } else {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                }
            })
            .catch(() => { /* falha silenciosa; validação server-side garante a integridade */ });
    });

    // Limpa o estado ao digitar novamente
    input.addEventListener('input', function () {
        this.classList.remove('is-invalid', 'is-valid');
    });
}());
</script>

<?php include 'partials/footer.php'; ?>
