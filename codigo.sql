-- Criar a tabela usuarios


-- Inserir um usuário administrador inicial
-- Senha padrão: admin123 (já com hash)


/*

voce nao precisa mais criar um div.sidebar, pois eu tenho um layout padrao para todas as páginas, é só usar o <?php include '../assets/layouts/sidebar.php'; ?> 

além disso, na parte do conteudo principal o div.page-header nao existe, use div.header invés disso!

E pegue todo o conteudo do body dessas páginas e coloque dentro de um div.dashboard por favor

*/


SET SQL_MODE='ALLOW_INVALID_DATES'; 

/* Lógico_1: */

CREATE TABLE IF NOT EXISTS USUARIOS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    nivel_acesso ENUM('admin', 'usuario') DEFAULT 'usuario',
    status BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE PROFESSOR (
    id_usuario INTEGER PRIMARY KEY,
    CPF VARCHAR(11)
);

CREATE TABLE CURSO (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(128),
    unidade VARCHAR(128),
    duracao VARCHAR(16),
    status BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE DISCIPLINA (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(128),
    codigo VARCHAR(16),
    carga_horaria INTEGER,
    metodo_avaliacao VARCHAR(64),
    descricao_avaliacao VARCHAR(256),
    descricao VARCHAR(512),
    id_curso INTEGER
);

CREATE TABLE TURMA (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(128),
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_final TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    carga_horaria INTEGER,
    id_curso INTEGER
);

CREATE TABLE ALUNO (
    id_usuario INTEGER PRIMARY KEY,
    RA INTEGER,
    data_nascimento DATE,
    status BOOLEAN
);

CREATE TABLE AVALIACAO (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    id_disciplina INTEGER,
    nome VARCHAR(128),
    peso DECIMAL(5,2),
    tipo VARCHAR(64)
);

CREATE TABLE NOTA (
    id_aluno INTEGER,
    id_avaliacao INTEGER,
    nota DECIMAL(5,2),
    data_hora TIMESTAMP,
    PRIMARY KEY (id_aluno, id_avaliacao)
);

CREATE TABLE MATRICULA (
    id_aluno INTEGER,
    id_turma INTEGER,
    RM INTEGER,
    data_hora TIMESTAMP,
    status BOOLEAN,
    PRIMARY KEY (id_aluno, id_turma)
);

CREATE TABLE AULA (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    id_professor INTEGER,
    id_turma INTEGER,
    id_disciplina INTEGER,
    descricao VARCHAR(512),
    data_hora_inicio TIMESTAMP,
    data_hora_final TIMESTAMP
);

CREATE TABLE FALTA (
    id_aluno INTEGER,
    id_aula INTEGER,
    PRIMARY KEY (id_aluno, id_aula)
);

CREATE TABLE HISTORICO (
    id_aluno INTEGER,
    id_disciplina INTEGER,
    ano INTEGER,
    semestre INTEGER,
    status VARCHAR(64),
    PRIMARY KEY (id_aluno, id_disciplina, ano)
);

CREATE TABLE LECIONA (
    id_professor INTEGER,
    id_disciplina INTEGER,
    id_turma INTEGER,
    PRIMARY KEY (id_professor, id_disciplina, id_turma)
);
 
ALTER TABLE LECIONA ADD CONSTRAINT FK_LECIONA_2
    FOREIGN KEY (id_professor)
    REFERENCES PROFESSOR (id_usuario);
 
ALTER TABLE LECIONA ADD CONSTRAINT FK_LECIONA_3
    FOREIGN KEY (id_disciplina)
    REFERENCES DISCIPLINA (id);

ALTER TABLE LECIONA ADD CONSTRAINT FK_LECIONA_4
    FOREIGN KEY (id_turma)
    REFERENCES TURMA (id);
 
ALTER TABLE PROFESSOR ADD CONSTRAINT FK_PROFESSOR_2
    FOREIGN KEY (id_usuario)
    REFERENCES USUARIOS (id)
    ON DELETE CASCADE;
 
ALTER TABLE DISCIPLINA ADD CONSTRAINT FK_DISCIPLINA_2
    FOREIGN KEY (id_curso)
    REFERENCES CURSO (id)
    ON DELETE CASCADE;
 
ALTER TABLE TURMA ADD CONSTRAINT FK_TURMA_2
    FOREIGN KEY (id_curso)
    REFERENCES CURSO (id)
    ON DELETE CASCADE;
 
ALTER TABLE ALUNO ADD CONSTRAINT FK_ALUNO_1
    FOREIGN KEY (id_usuario)
    REFERENCES USUARIOS (id)
    ON DELETE CASCADE;
 
ALTER TABLE AVALIACAO ADD CONSTRAINT FK_AVALIACAO_1
    FOREIGN KEY (id_disciplina)
    REFERENCES DISCIPLINA (id)
    ON DELETE CASCADE;
 
ALTER TABLE NOTA ADD CONSTRAINT FK_NOTA_1
    FOREIGN KEY (id_aluno)
    REFERENCES ALUNO (id_usuario)
    ON DELETE CASCADE;

ALTER TABLE NOTA ADD CONSTRAINT FK_NOTA_2
    FOREIGN KEY (id_avaliacao)
    REFERENCES AVALIACAO (id)
    ON DELETE CASCADE;
 
ALTER TABLE MATRICULA ADD CONSTRAINT FK_MATRICULA_1
    FOREIGN KEY (id_aluno)
    REFERENCES ALUNO (id_usuario)
    ON DELETE CASCADE;
 
ALTER TABLE MATRICULA ADD CONSTRAINT FK_MATRICULA_2
    FOREIGN KEY (id_turma)
    REFERENCES TURMA (id)
    ON DELETE CASCADE;
 
ALTER TABLE AULA ADD CONSTRAINT FK_AULA_1
    FOREIGN KEY (id_professor)
    REFERENCES PROFESSOR (id_usuario)
    ON DELETE NO ACTION;
 
ALTER TABLE AULA ADD CONSTRAINT FK_AULA_2
    FOREIGN KEY (id_turma)
    REFERENCES TURMA (id)
    ON DELETE NO ACTION;
 
ALTER TABLE AULA ADD CONSTRAINT FK_AULA_3
    FOREIGN KEY (id_disciplina)
    REFERENCES DISCIPLINA (id)
    ON DELETE NO ACTION;
 
ALTER TABLE FALTA ADD CONSTRAINT FK_FALTA_1
    FOREIGN KEY (id_aluno)
    REFERENCES ALUNO (id_usuario)
    ON DELETE CASCADE;

ALTER TABLE FALTA ADD CONSTRAINT FK_FALTA_2
    FOREIGN KEY (id_aula)
    REFERENCES AULA (id)
    ON DELETE CASCADE;
 
ALTER TABLE HISTORICO ADD CONSTRAINT FK_HISTORICO_1
    FOREIGN KEY (id_aluno)
    REFERENCES ALUNO (id_usuario)
    ON DELETE CASCADE;
 
ALTER TABLE HISTORICO ADD CONSTRAINT FK_HISTORICO_2
    FOREIGN KEY (id_disciplina)
    REFERENCES DISCIPLINA (id)
    ON DELETE CASCADE;

INSERT INTO usuarios (nome, usuario, senha, email, nivel_acesso) VALUES 
('Administrador', 'admin', '$2y$10$XGTFx8aTDgCy9nMVoIF7buLaSFkXZwcs9A8BqH2IyDUFF7F0taPZq', 'admin@exemplo.com', 'admin');
