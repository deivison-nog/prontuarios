<?php
// ============================================================
// Funções utilitárias
// ============================================================

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatarData(string|null $data): string
{
    if (empty($data)) {
        return '';
    }
    // Tenta converter formato ISO (YYYY-MM-DD) para DD/MM/YYYY
    $ts = strtotime($data);
    return $ts !== false ? date('d/m/Y', $ts) : h($data);
}

function dataBrParaIso(string $data): string
{
    // Converte DD/MM/YYYY para YYYY-MM-DD
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', trim($data), $m)) {
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    }
    return $data;
}

function validarData(string $data): bool
{
    if (empty($data)) {
        return true; // campo opcional
    }
    // Aceita YYYY-MM-DD (input type=date) ou DD/MM/YYYY
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        [$y, $m, $d] = explode('-', $data);
        return checkdate((int)$m, (int)$d, (int)$y);
    }
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
        [$d, $m, $y] = explode('/', $data);
        return checkdate((int)$m, (int)$d, (int)$y);
    }
    return false;
}

function redirecionarComMensagem(string $url, string $tipo, string $mensagem): void
{
    $_SESSION['flash_tipo']     = $tipo;
    $_SESSION['flash_mensagem'] = $mensagem;
    header("Location: $url");
    exit;
}

function obterFlash(): array|null
{
    if (!empty($_SESSION['flash_mensagem'])) {
        $flash = [
            'tipo'     => $_SESSION['flash_tipo']     ?? 'info',
            'mensagem' => $_SESSION['flash_mensagem'],
        ];
        unset($_SESSION['flash_tipo'], $_SESSION['flash_mensagem']);
        return $flash;
    }
    return null;
}

function sanitizarPost(array $campos): array
{
    $saida = [];
    foreach ($campos as $campo) {
        $saida[$campo] = trim($_POST[$campo] ?? '');
    }
    return $saida;
}

/**
 * Renderiza um campo de visualização somente-leitura.
 */
function campoVisualizacao(string $label, mixed $valor): void
{
    $v = is_bool($valor) ? ($valor ? 'Sim' : 'Não') : (string)($valor ?? '');
    echo '<div class="view-field">';
    echo '<div class="view-label">' . h($label) . '</div>';
    echo '<div class="view-value">' . (trim($v) !== '' ? h($v) : '<span class="text-muted">—</span>') . '</div>';
    echo '</div>';
}
