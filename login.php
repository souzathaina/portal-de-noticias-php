<?php
session_start();  // ESSENCIAL para usar sessão

require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    // Busca também o campo foto
    $sql = "SELECT id, nome, senha, foto FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario["senha"])) {
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        // Se foto estiver vazia, colocar imagem padrão
        $_SESSION['foto'] = !empty($usuario['foto']) ? $usuario['foto'] : 'imagens/perfil_padrao.png';

        header("location: telaLogado.php");
        exit;
    } else {
        $mensagem = "E-mail ou senha incorreto";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/style_login.css">

    <title>Login</title>
</head>

<body>
    <h2>Login</h2>
    <?php if (!empty($mensagem)): ?>
        <p style="color: red;"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Senha:</label>
        <input type="password" name="senha" required>

        <button type="submit">Entrar</button>

        <div class="links">
            <a href="cadastro.php">Cadastrar-se</a>
            <a href="index.php">Voltar</a>
            <a href="alterarSenha.php">Esqueceu a senha?</a>
        </div>
    </form>

</body>

</html>