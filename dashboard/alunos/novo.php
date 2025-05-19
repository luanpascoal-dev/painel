<?php
session_start();

include '../assets/layouts/verify.php';

$success_message = '';
$error_message = '';

// Gerar RA único
function gerarRA($conn) {
    do {
        $ra = mt_rand(10000, 99999);
        $stmt = $conn->prepare("SELECT id_usuario FROM ALUNO WHERE RA = ?");
        $stmt->bind_param("i", $ra);
        $stmt->execute();
    } while ($stmt->get_result()->num_rows > 0);
    return $ra;
}

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $data_nascimento = $_POST['data_nascimento'];
    
    // Validações
    if(empty($nome) || empty($usuario) || empty($email) || empty($senha) || empty($data_nascimento)) {
        $error_message = "Todos os campos são obrigatórios";
    } else {
        // Verificar se usuário já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ?");
        $stmt->bind_param("ss", $usuario, $email);
        $stmt->execute();
        if($stmt->get_result()->num_rows > 0) {
            $error_message = "Este usuário ou email já está em uso";
        } else {
            // Iniciar transação
            $conn->begin_transaction();
            
            try {
                // Criar usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, usuario, email, senha, nivel_acesso, status) VALUES (?, ?, ?, ?, 'aluno', 1)");
                $stmt->bind_param("ssss", $nome, $usuario, $email, $senha_hash);
                $stmt->execute();
                
                $id_usuario = $conn->insert_id;
                $ra = gerarRA($conn);
                
                // Criar aluno
                $stmt = $conn->prepare("INSERT INTO ALUNO (id_usuario, RA, data_nascimento, status) VALUES (?, ?, ?, 1)");
                $stmt->bind_param("iis", $id_usuario, $ra, $data_nascimento);
                $stmt->execute();
                
                // Confirmar transação
                $conn->commit();
                
                $success_message = "Aluno cadastrado com sucesso! RA: " . $ra;
                // Limpar os campos após sucesso
                $nome = $usuario = $email = $data_nascimento = '';
                
            } catch (Exception $e) {
                // Reverter em caso de erro
                $conn->rollback();
                $error_message = "Erro ao cadastrar aluno: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Aluno - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Novo Aluno</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle fa-2x"></i>
                    <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </div>
            </div>

            <div class="form-container">
                <?php if($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario">Nome de Usuário</label>
                        <div class="input-group">
                            <input type="text" name="usuario" id="usuario" required>
                            <button type="button" class="btn-secondary" onclick="gerarUsuario()" title="Gerar usuário">
                                <i class="fas fa-magic"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo isset($data_nascimento) ? htmlspecialchars($data_nascimento) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Criar Aluno
                        </button>
                        <a href="./" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
async function gerarUsuario() {
    const nomeInput = document.getElementById('nome');
    const usuarioInput = document.getElementById('usuario');
    
    if (!nomeInput.value) {
        alert('Digite o nome primeiro!');
        nomeInput.focus();
        return;
    }

    // Função para normalizar o texto
    function normalizar(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Remove acentos
            .replace(/[^a-z0-9]/g, ''); // Remove caracteres especiais
    }

    const nome = nomeInput.value;
    const nomes = nome.split(' ').filter(n => n.length > 2); // Ignora palavras pequenas como "de", "da"
    
    let usuario = '';
    
    if (nomes.length >= 2) {
        // Primeira letra do primeiro nome + último nome
        usuario = normalizar(nomes[0].charAt(0) + nomes[nomes.length - 1]);
    } else {
        // Se só tem um nome, usa ele
        usuario = normalizar(nomes[0]);
    }

    // Verificar se o usuário já existe
    try {
        const response = await fetch('verificar_usuario.php?usuario=' + usuario);
        const data = await response.json();
        
        if (data.exists) {
            // Se existe, adiciona números aleatórios até encontrar um disponível
            let tentativa = 1;
            let novoUsuario = usuario;
            
            while (tentativa <= 100) { // Limite de 100 tentativas
                novoUsuario = usuario + Math.floor(Math.random() * 999);
                const checkResponse = await fetch('verificar_usuario.php?usuario=' + novoUsuario);
                const checkData = await checkResponse.json();
                
                if (!checkData.exists) {
                    usuario = novoUsuario;
                    break;
                }
                tentativa++;
            }
        }
        
        usuarioInput.value = usuario;
        
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao verificar usuário');
    }
}
</script>
</body>
</html> 