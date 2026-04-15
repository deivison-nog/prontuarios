<?php
/**
 * Partial: bloco de atendimento individual
 *
 * Variáveis esperadas:
 *   $idx  — índice do bloco (int ou '__INDEX__' para o template JS)
 *   $at   — array com os dados do atendimento (chaves = campos)
 */
?>
<div class="atendimento-bloco border rounded p-3 mb-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-semibold mb-0 text-primary">
            <i class="bi bi-heart-pulse me-1"></i>
            Atendimento <span class="numero-atendimento">#<?= is_int($idx) ? $idx + 1 : 1 ?></span>
        </h6>
        <button type="button" class="btn btn-sm btn-outline-danger btn-remover-atendimento" style="display:none;">
            <i class="bi bi-trash me-1"></i>Remover
        </button>
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Data do Atendimento</label>
            <input type="date" class="form-control"
                name="atendimentos[<?= $idx ?>][data_atendimento]"
                value="<?= h($at['data_atendimento'] ?? '') ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Idade na Época</label>
            <input type="text" class="form-control"
                name="atendimentos[<?= $idx ?>][idade]"
                value="<?= h($at['idade'] ?? '') ?>" placeholder="Ex.: 45 anos">
        </div>

        <div class="col-md-3">
            <label class="form-label">Programa</label>
            <input type="text" class="form-control"
                name="atendimentos[<?= $idx ?>][programa]"
                value="<?= h($at['programa'] ?? '') ?>" placeholder="Ex.: ESF">
        </div>

        <div class="col-md-3">
            <label class="form-label">Grupo Alvo</label>
            <input type="text" class="form-control"
                name="atendimentos[<?= $idx ?>][grupo_alvo]"
                value="<?= h($at['grupo_alvo'] ?? '') ?>" placeholder="Ex.: Idoso">
        </div>

        <div class="col-md-4">
            <label class="form-label">Atividade</label>
            <input type="text" class="form-control"
                name="atendimentos[<?= $idx ?>][atividade]"
                value="<?= h($at['atividade'] ?? '') ?>" placeholder="Ex.: Consulta">
        </div>

        <div class="col-md-4">
            <label class="form-label">Serviço</label>
            <input type="text" class="form-control"
                name="atendimentos[<?= $idx ?>][servico]"
                value="<?= h($at['servico'] ?? '') ?>" placeholder="Ex.: Clínica Geral">
        </div>

        <div class="col-md-12">
            <label class="form-label">Diagnóstico</label>
            <textarea class="form-control"
                name="atendimentos[<?= $idx ?>][diagnostico]" rows="3"
                placeholder="CID, hipótese diagnóstica..."><?= h($at['diagnostico'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Prescrição</label>
            <textarea class="form-control"
                name="atendimentos[<?= $idx ?>][prescricao]" rows="3"
                placeholder="Medicamentos prescritos..."><?= h($at['prescricao'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">Tratamento</label>
            <textarea class="form-control"
                name="atendimentos[<?= $idx ?>][tratamento]" rows="3"
                placeholder="Procedimentos, terapias..."><?= h($at['tratamento'] ?? '') ?></textarea>
        </div>

        <div class="col-md-12">
            <label class="form-label">Evolução</label>
            <textarea class="form-control"
                name="atendimentos[<?= $idx ?>][evolucao]" rows="4"
                placeholder="Evolução clínica do paciente..."><?= h($at['evolucao'] ?? '') ?></textarea>
        </div>

        <div class="col-md-12">
            <label class="form-label">Observações</label>
            <textarea class="form-control"
                name="atendimentos[<?= $idx ?>][observacoes]" rows="3"
                placeholder="Informações adicionais..."><?= h($at['observacoes'] ?? '') ?></textarea>
        </div>
    </div>
</div>
