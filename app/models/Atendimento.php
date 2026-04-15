<?php
// ============================================================
// Model: Atendimento
// ============================================================

class Atendimento
{
    public function __construct(private PDO $pdo) {}

    public function listarPorProntuario(int $prontuarioId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM atendimentos
             WHERE prontuario_id = ?
             ORDER BY data_atendimento ASC, id ASC'
        );
        $stmt->execute([$prontuarioId]);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM atendimentos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function inserir(int $prontuarioId, array $dados): int
    {
        $sql = 'INSERT INTO atendimentos (
                    prontuario_id, data_atendimento, programa, grupo_alvo,
                    atividade, servico, idade, diagnostico, prescricao,
                    tratamento, evolucao, observacoes
                ) VALUES (
                    :prontuario_id, :data_atendimento, :programa, :grupo_alvo,
                    :atividade, :servico, :idade, :diagnostico, :prescricao,
                    :tratamento, :evolucao, :observacoes
                )';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->prepararDados($prontuarioId, $dados));
        return (int)$this->pdo->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sql = 'UPDATE atendimentos SET
                    data_atendimento = :data_atendimento,
                    programa         = :programa,
                    grupo_alvo       = :grupo_alvo,
                    atividade        = :atividade,
                    servico          = :servico,
                    idade            = :idade,
                    diagnostico      = :diagnostico,
                    prescricao       = :prescricao,
                    tratamento       = :tratamento,
                    evolucao         = :evolucao,
                    observacoes      = :observacoes
                WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $params = $this->prepararDados(0, $dados);
        unset($params[':prontuario_id']);
        $params[':id'] = $id;
        return $stmt->execute($params);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM atendimentos WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // --------------------------------------------------------
    private function prepararDados(int $prontuarioId, array $d): array
    {
        return [
            ':prontuario_id'    => $prontuarioId,
            ':data_atendimento' => $d['data_atendimento'] ?: null,
            ':programa'         => $d['programa']         ?: null,
            ':grupo_alvo'       => $d['grupo_alvo']       ?: null,
            ':atividade'        => $d['atividade']        ?: null,
            ':servico'          => $d['servico']          ?: null,
            ':idade'            => $d['idade']            ?: null,
            ':diagnostico'      => $d['diagnostico']      ?: null,
            ':prescricao'       => $d['prescricao']       ?: null,
            ':tratamento'       => $d['tratamento']       ?: null,
            ':evolucao'         => $d['evolucao']         ?: null,
            ':observacoes'      => $d['observacoes']      ?: null,
        ];
    }
}
