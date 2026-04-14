-- ============================================================
-- Prontuários - Esquema do Banco de Dados
-- ============================================================

CREATE DATABASE IF NOT EXISTS prontuarios CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prontuarios;

-- ------------------------------------------------------------
-- Tabela de usuários do sistema
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(120)  NOT NULL,
    email       VARCHAR(180)  NOT NULL UNIQUE,
    senha       VARCHAR(255)  NOT NULL,
    perfil      ENUM('digitador','supervisor','admin') NOT NULL DEFAULT 'digitador',
    ativo       TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabela principal de prontuários
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS prontuarios (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT UNSIGNED NOT NULL,

    -- Identificação
    numero_prontuario   VARCHAR(50)  DEFAULT NULL,
    nome                VARCHAR(255) NOT NULL,
    data_nascimento     DATE         DEFAULT NULL,
    sexo                ENUM('Masculino','Feminino','Outro') DEFAULT NULL,
    estado_civil        VARCHAR(50)  DEFAULT NULL,
    profissao           VARCHAR(100) DEFAULT NULL,
    nome_pai            VARCHAR(255) DEFAULT NULL,
    nome_mae            VARCHAR(255) DEFAULT NULL,
    municipio           VARCHAR(100) DEFAULT NULL,
    endereco            VARCHAR(255) DEFAULT NULL,
    cliente             VARCHAR(255) DEFAULT NULL,
    beneficiario        VARCHAR(255) DEFAULT NULL,
    obito               TINYINT(1)   NOT NULL DEFAULT 0,
    data_obito          DATE         DEFAULT NULL,
    causa_obito         VARCHAR(255) DEFAULT NULL,

    -- Atendimento / Evolução
    data_atendimento    DATE         DEFAULT NULL,
    programa            VARCHAR(100) DEFAULT NULL,
    grupo_alvo          VARCHAR(100) DEFAULT NULL,
    atividade           VARCHAR(100) DEFAULT NULL,
    servico             VARCHAR(100) DEFAULT NULL,
    idade               VARCHAR(20)  DEFAULT NULL,
    diagnostico         TEXT         DEFAULT NULL,
    prescricao          TEXT         DEFAULT NULL,
    tratamento          TEXT         DEFAULT NULL,
    evolucao            TEXT         DEFAULT NULL,
    observacoes         TEXT         DEFAULT NULL,

    -- Controle
    status              ENUM('pendente','digitado','revisado') NOT NULL DEFAULT 'digitado',
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_prontuarios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Usuário administrador padrão
-- Senha: admin123  (troque imediatamente em produção)
-- ------------------------------------------------------------
INSERT INTO usuarios (nome, email, senha, perfil) VALUES
(
    'Administrador',
    'admin@prontuarios.local',
    '$2y$12$YbW3k5T.q5OhNv/5N4O5xuasBfpXVaVqYQ3..4o8n/Nfq/dJZUa.C',
    'admin'
);
-- senha hash gerada com password_hash('admin123', PASSWORD_BCRYPT, ['cost'=>12])
