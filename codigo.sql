-- Criar a tabela usuarios
CREATE TABLE IF NOT EXISTS usuarios (
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

-- Inserir um usuário administrador inicial
-- Senha padrão: admin123 (já com hash)
INSERT INTO usuarios (nome, usuario, senha, email, nivel_acesso) VALUES 
('Administrador', 'admin', '$2y$10$XGTFx8aTDgCy9nMVoIF7buLaSFkXZwcs9A8BqH2IyDUFF7F0taPZq', 'admin@exemplo.com', 'admin');