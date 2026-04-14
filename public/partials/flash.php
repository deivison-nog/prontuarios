<?php
$flash = obterFlash();
if ($flash):
    $tipo = match($flash['tipo']) {
        'sucesso'  => 'success',
        'erro'     => 'danger',
        'aviso'    => 'warning',
        default    => 'info',
    };
?>
<div class="alert alert-<?= $tipo ?> alert-dismissible fade show" role="alert">
    <?php if ($flash['tipo'] === 'sucesso'): ?>
        <i class="bi bi-check-circle-fill me-2"></i>
    <?php elseif ($flash['tipo'] === 'erro'): ?>
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?php else: ?>
        <i class="bi bi-info-circle-fill me-2"></i>
    <?php endif; ?>
    <?= h($flash['mensagem']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
<?php endif; ?>
