<?php
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';


$mensagem = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);
    $sql = "SELECT id, nome, senha FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare(($sql));
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    //verifica se encontrou o usuário e se a senha está correta
    if ($usuario && password_verify($senha, $usuario["senha"])) {
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];

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
    <title>Login</title>
</head>

<body>
    <h2>Login</h2>
    <?php if (!empty($mensagem)): ?>
        <p style="color: red;"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Entrar</button>
        <a href="cadastro.php">Cadastrar-se</a>
        <a href="index.php">Voltar</a>
    </form>
</body>

</html>