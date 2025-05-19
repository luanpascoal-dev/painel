-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 04:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `painel`
--

-- --------------------------------------------------------

--
-- Table structure for table `aluno`
--

CREATE TABLE `aluno` (
  `id_usuario` int(11) NOT NULL,
  `RA` int(11) DEFAULT NULL,
  `data_nascimento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aluno`
--

INSERT INTO `aluno` (`id_usuario`, `RA`, `data_nascimento`, `status`) VALUES
(2, 97759, '2006-02-09 03:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `aula`
--

CREATE TABLE `aula` (
  `id` int(11) NOT NULL,
  `id_professor` int(11) DEFAULT NULL,
  `id_turma` int(11) DEFAULT NULL,
  `id_disciplina` int(11) DEFAULT NULL,
  `descricao` varchar(512) DEFAULT NULL,
  `data_hora_inicio` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data_hora_final` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aula`
--

INSERT INTO `aula` (`id`, `id_professor`, `id_turma`, `id_disciplina`, `descricao`, `data_hora_inicio`, `data_hora_final`) VALUES
(1, 2, 1, 1, NULL, '2025-05-19 16:00:00', '2025-05-19 16:50:00'),
(2, NULL, 1, 1, 'Aula Teste', '2025-05-19 16:50:00', '2025-05-19 17:40:00'),
(3, NULL, 1, 1, 'Aula Teste', '2025-05-19 17:50:00', '2025-05-19 18:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `avaliacao`
--

CREATE TABLE `avaliacao` (
  `id` int(11) NOT NULL,
  `id_disciplina` int(11) DEFAULT NULL,
  `nome` varchar(128) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `tipo` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `avaliacao`
--

INSERT INTO `avaliacao` (`id`, `id_disciplina`, `nome`, `peso`, `tipo`) VALUES
(2, 1, 'P1', 10.00, 'Prova'),
(3, 1, 'P2', 5.00, 'Prova');

-- --------------------------------------------------------

--
-- Table structure for table `curso`
--

CREATE TABLE `curso` (
  `id` int(11) NOT NULL,
  `nome` varchar(128) DEFAULT NULL,
  `unidade` varchar(128) DEFAULT NULL,
  `duracao` varchar(16) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `curso`
--

INSERT INTO `curso` (`id`, `nome`, `unidade`, `duracao`, `status`, `data_criacao`, `ultima_atualizacao`) VALUES
(2, 'Python 1.0', 'FATEC Jundiaí', '6 meses', 1, '2025-05-16 02:59:07', '2025-05-16 02:59:07');

-- --------------------------------------------------------

--
-- Table structure for table `disciplina`
--

CREATE TABLE `disciplina` (
  `id` int(11) NOT NULL,
  `nome` varchar(128) DEFAULT NULL,
  `codigo` varchar(16) DEFAULT NULL,
  `carga_horaria` int(11) DEFAULT NULL,
  `metodo_avaliacao` varchar(64) DEFAULT NULL,
  `descricao_avaliacao` varchar(256) DEFAULT NULL,
  `descricao` varchar(512) DEFAULT NULL,
  `id_curso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disciplina`
--

INSERT INTO `disciplina` (`id`, `nome`, `codigo`, `carga_horaria`, `metodo_avaliacao`, `descricao_avaliacao`, `descricao`, `id_curso`) VALUES
(1, 'Algoritmo', 'ALG100', 2, '(P1 + P2) / 2', '', '', 2);

-- --------------------------------------------------------

--
-- Table structure for table `falta`
--

CREATE TABLE `falta` (
  `id_aluno` int(11) NOT NULL,
  `id_aula` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historico`
--

CREATE TABLE `historico` (
  `id_aluno` int(11) NOT NULL,
  `id_disciplina` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `semestre` int(11) DEFAULT NULL,
  `status` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leciona`
--

CREATE TABLE `leciona` (
  `id_professor` int(11) NOT NULL,
  `id_disciplina` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leciona`
--

INSERT INTO `leciona` (`id_professor`, `id_disciplina`, `id_turma`) VALUES
(2, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `matricula`
--

CREATE TABLE `matricula` (
  `id_aluno` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `RM` int(11) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matricula`
--

INSERT INTO `matricula` (`id_aluno`, `id_turma`, `RM`, `data_hora`, `status`) VALUES
(2, 1, 20250001, '2025-05-18 20:32:02', 0),
(2, 2, 20250002, '2025-05-17 03:11:01', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nota`
--

CREATE TABLE `nota` (
  `id_aluno` int(11) NOT NULL,
  `id_avaliacao` int(11) NOT NULL,
  `nota` decimal(5,2) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nota`
--

INSERT INTO `nota` (`id_aluno`, `id_avaliacao`, `nota`, `data_hora`) VALUES
(2, 2, 10.00, '2025-05-19 01:32:10'),
(2, 3, 5.00, '2025-05-19 01:32:14');

-- --------------------------------------------------------

--
-- Table structure for table `professor`
--

CREATE TABLE `professor` (
  `id_usuario` int(11) NOT NULL,
  `CPF` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professor`
--

INSERT INTO `professor` (`id_usuario`, `CPF`) VALUES
(2, '11111111111');

-- --------------------------------------------------------

--
-- Table structure for table `turma`
--

CREATE TABLE `turma` (
  `id` int(11) NOT NULL,
  `nome` varchar(128) DEFAULT NULL,
  `data_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_final` timestamp NOT NULL DEFAULT current_timestamp(),
  `carga_horaria` int(11) DEFAULT NULL,
  `id_curso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `turma`
--

INSERT INTO `turma` (`id`, `nome`, `data_inicio`, `data_final`, `carga_horaria`, `id_curso`) VALUES
(1, 'PY20251', '2025-03-01 03:00:00', '2025-05-01 03:00:00', 20, 2),
(2, 'PY20242', '2024-08-01 03:00:00', '2025-06-30 03:00:00', 60, 2);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nivel_acesso` enum('admin','usuario') DEFAULT 'usuario',
  `status` tinyint(1) DEFAULT 1,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `email`, `nivel_acesso`, `status`, `data_cadastro`, `ultima_atualizacao`) VALUES
(2, 'Luan Pascoal', 'luanpascoal', '$2y$10$JQhlyr6aOxPmSvGbxQh1Qun33jnYAF0B5px3icF0a5Br176oeKaK2', 'luanpascoal@gmail.com', 'admin', 1, '2025-05-16 05:15:21', '2025-05-16 05:15:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aluno`
--
ALTER TABLE `aluno`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indexes for table `aula`
--
ALTER TABLE `aula`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_AULA_1` (`id_professor`),
  ADD KEY `FK_AULA_2` (`id_turma`),
  ADD KEY `FK_AULA_3` (`id_disciplina`);

--
-- Indexes for table `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_AVALIACAO_1` (`id_disciplina`);

--
-- Indexes for table `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disciplina`
--
ALTER TABLE `disciplina`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_DISCIPLINA_2` (`id_curso`);

--
-- Indexes for table `falta`
--
ALTER TABLE `falta`
  ADD PRIMARY KEY (`id_aluno`,`id_aula`),
  ADD KEY `FK_FALTA_2` (`id_aula`);

--
-- Indexes for table `historico`
--
ALTER TABLE `historico`
  ADD PRIMARY KEY (`id_aluno`,`id_disciplina`,`ano`),
  ADD KEY `FK_HISTORICO_2` (`id_disciplina`);

--
-- Indexes for table `leciona`
--
ALTER TABLE `leciona`
  ADD PRIMARY KEY (`id_professor`,`id_disciplina`),
  ADD KEY `FK_LECIONA_3` (`id_disciplina`);

--
-- Indexes for table `matricula`
--
ALTER TABLE `matricula`
  ADD PRIMARY KEY (`id_aluno`,`id_turma`),
  ADD KEY `FK_MATRICULA_2` (`id_turma`);

--
-- Indexes for table `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`id_aluno`,`id_avaliacao`),
  ADD KEY `FK_NOTA_2` (`id_avaliacao`);

--
-- Indexes for table `professor`
--
ALTER TABLE `professor`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indexes for table `turma`
--
ALTER TABLE `turma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_TURMA_2` (`id_curso`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aula`
--
ALTER TABLE `aula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `avaliacao`
--
ALTER TABLE `avaliacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `curso`
--
ALTER TABLE `curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `disciplina`
--
ALTER TABLE `disciplina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `turma`
--
ALTER TABLE `turma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aluno`
--
ALTER TABLE `aluno`
  ADD CONSTRAINT `FK_ALUNO_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `aula`
--
ALTER TABLE `aula`
  ADD CONSTRAINT `FK_AULA_1` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_usuario`) ON DELETE NO ACTION,
  ADD CONSTRAINT `FK_AULA_2` FOREIGN KEY (`id_turma`) REFERENCES `turma` (`id`) ON DELETE NO ACTION,
  ADD CONSTRAINT `FK_AULA_3` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id`) ON DELETE NO ACTION;

--
-- Constraints for table `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD CONSTRAINT `FK_AVALIACAO_1` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disciplina`
--
ALTER TABLE `disciplina`
  ADD CONSTRAINT `FK_DISCIPLINA_2` FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `falta`
--
ALTER TABLE `falta`
  ADD CONSTRAINT `FK_FALTA_1` FOREIGN KEY (`id_aluno`) REFERENCES `aluno` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_FALTA_2` FOREIGN KEY (`id_aula`) REFERENCES `aula` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historico`
--
ALTER TABLE `historico`
  ADD CONSTRAINT `FK_HISTORICO_1` FOREIGN KEY (`id_aluno`) REFERENCES `aluno` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_HISTORICO_2` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leciona`
--
ALTER TABLE `leciona`
  ADD CONSTRAINT `FK_LECIONA_2` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_usuario`),
  ADD CONSTRAINT `FK_LECIONA_3` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id`);

--
-- Constraints for table `matricula`
--
ALTER TABLE `matricula`
  ADD CONSTRAINT `FK_MATRICULA_1` FOREIGN KEY (`id_aluno`) REFERENCES `aluno` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_MATRICULA_2` FOREIGN KEY (`id_turma`) REFERENCES `turma` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nota`
--
ALTER TABLE `nota`
  ADD CONSTRAINT `FK_NOTA_1` FOREIGN KEY (`id_aluno`) REFERENCES `aluno` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_NOTA_2` FOREIGN KEY (`id_avaliacao`) REFERENCES `avaliacao` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `professor`
--
ALTER TABLE `professor`
  ADD CONSTRAINT `FK_PROFESSOR_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `turma`
--
ALTER TABLE `turma`
  ADD CONSTRAINT `FK_TURMA_2` FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
