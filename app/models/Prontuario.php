<?php
// ============================================================
// Model: Prontuario
// ============================================================

class Prontuario
{
    public function __construct(private PDO $pdo) {}

    public function listar(string $busca = '', int $limite = 50, int $offset = 0): array
    {
        $busca = "%{$busca}%";
        $sql = 'SELECT p.*, u.nome AS digitador
                FROM prontuarios p
                JOIN usuarios u ON u.id = p.usuario_id
                WHERE (p.nome LIKE ? OR p.numero_prontuario LIKE ? OR p.municipio LIKE ?)
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$busca, $busca, $busca, $limite, $offset]);
        return $stmt->fetchAll();
    }

    public function contarTodos(string $busca = ''): int
    {
        $busca = "%{$busca}%";
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM prontuarios
             WHERE nome LIKE ? OR numero_prontuario LIKE ? OR municipio LIKE ?'
        );
        $stmt->execute([$busca, $busca, $busca]);
        return (int)$stmt->fetchColumn();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, u.nome AS digitador
             FROM prontuarios p
             JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function inserir(array $dados): int
    {
        $sql = 'INSERT INTO prontuarios (
                    usuario_id, numero_prontuario, nome, data_nascimento, sexo,
                    estado_civil, profissao, nome_pai, nome_mae, municipio, endereco,
                    cliente, beneficiario, obito, data_obito, causa_obito,
                    data_atendimento, programa, grupo_alvo, atividade, servico,
                    idade, diagnostico, prescricao, tratamento, evolucao, observacoes, status
                ) VALUES (
                    :usuario_id, :numero_prontuario, :nome, :data_nascimento, :sexo,
                    :estado_civil, :profissao, :nome_pai, :nome_mae, :municipio, :endereco,
                    :cliente, :beneficiario, :obito, :data_obito, :causa_obito,
                    :data_atendimento, :programa, :grupo_alvo, :atividade, :servico,
                    :idade, :diagnostico, :prescricao, :tratamento, :evolucao, :observacoes, :status
                )';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->prepararDados($dados));
        return (int)$this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = 'UPDATE prontuarios SET
                    numero_prontuario = :numero_prontuario,
                    nome              = :nome,
                    data_nascimento   = :data_nascimento,
                    sexo              = :sexo,
                    estado_civil      = :estado_civil,
                    profissao         = :profissao,
                    nome_pai          = :nome_pai,
                    nome_mae          = :nome_mae,
                    municipio         = :municipio,
                    endereco          = :endereco,
                    cliente           = :cliente,
                    beneficiario      = :beneficiario,
                    obito             = :obito,
                    data_obito        = :data_obito,
                    causa_obito       = :causa_obito,
                    data_atendimento  = :data_atendimento,
                    programa          = :programa,
                    grupo_alvo        = :grupo_alvo,
                    atividade         = :atividade,
                    servico           = :servico,
                    idade             = :idade,
                    diagnostico       = :diagnostico,
                    prescricao        = :prescricao,
                    tratamento        = :tratamento,
                    evolucao          = :evolucao,
                    observacoes       = :observacoes,
                    status            = :status
                WHERE id = :id';
        $params = $this->prepararDados($dados);
        $params[':id'] = $id;
        unset($params[':usuario_id']);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function numeroProntuarioExiste(string $numero, int $excludeId = 0): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM prontuarios WHERE numero_prontuario = ? AND id <> ?'
        );
        $stmt->execute([$numero, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM prontuarios WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function estatisticas(): array
    {
        $total      = (int)$this->pdo->query('SELECT COUNT(*) FROM prontuarios')->fetchColumn();
        $hoje       = (int)$this->pdo->query("SELECT COUNT(*) FROM prontuarios WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $semana     = (int)$this->pdo->query("SELECT COUNT(*) FROM prontuarios WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $revisados  = (int)$this->pdo->query("SELECT COUNT(*) FROM prontuarios WHERE status = 'revisado'")->fetchColumn();
        return compact('total', 'hoje', 'semana', 'revisados');
    }

    public function recentes(int $limite = 8): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id, p.nome, p.numero_prontuario, p.municipio, p.status, p.created_at, u.nome AS digitador
             FROM prontuarios p
             JOIN usuarios u ON u.id = p.usuario_id
             ORDER BY p.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }

    // --------------------------------------------------------
    private function prepararDados(array $d): array
    {
        return [
            ':usuario_id'        => $d['usuario_id']        ?? null,
            ':numero_prontuario' => $d['numero_prontuario']  ?: null,
            ':nome'              => $d['nome'],
            ':data_nascimento'   => $d['data_nascimento']    ?: null,
            ':sexo'              => $d['sexo']               ?: null,
            ':estado_civil'      => $d['estado_civil']       ?: null,
            ':profissao'         => $d['profissao']          ?: null,
            ':nome_pai'          => $d['nome_pai']           ?: null,
            ':nome_mae'          => $d['nome_mae']           ?: null,
            ':municipio'         => $d['municipio']          ?: null,
            ':endereco'          => $d['endereco']           ?: null,
            ':cliente'           => $d['cliente']            ?: null,
            ':beneficiario'      => $d['beneficiario']       ?: null,
            ':obito'             => isset($d['obito']) && $d['obito'] ? 1 : 0,
            ':data_obito'        => $d['data_obito']         ?: null,
            ':causa_obito'       => $d['causa_obito']        ?: null,
            ':data_atendimento'  => $d['data_atendimento']   ?: null,
            ':programa'          => $d['programa']           ?: null,
            ':grupo_alvo'        => $d['grupo_alvo']         ?: null,
            ':atividade'         => $d['atividade']          ?: null,
            ':servico'           => $d['servico']            ?: null,
            ':idade'             => $d['idade']              ?: null,
            ':diagnostico'       => $d['diagnostico']        ?: null,
            ':prescricao'        => $d['prescricao']         ?: null,
            ':tratamento'        => $d['tratamento']         ?: null,
            ':evolucao'          => $d['evolucao']           ?: null,
            ':observacoes'       => $d['observacoes']        ?: null,
            ':status'            => $d['status']             ?? 'digitado',
        ];
    }
}
