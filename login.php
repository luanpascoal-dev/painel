<?php
session_start();

// Verificação de usuário já logado
if(isset($_SESSION['id']) && isset($_SESSION['usuario'])) {
    header("Location: index");
    exit();
}

include 'database.php';

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, usuario, senha, nivel_acesso, status FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($senha, $user['senha']) && $user['status'] == 1) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['nivel_acesso'] = $user['nivel_acesso'];
            
            // Se for admin, redireciona para o dashboard, senão para a página inicial
            if($user['nivel_acesso'] == 'admin') {
                header("Location: dashboard/home");
            } else {
                header("Location: index");
            }
            exit();
        } else {
            $error_message = "Usuário ou senha inválidos";
        }
    } else {
        $error_message = "Usuário ou senha inválidos";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Entrar</h2>
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
