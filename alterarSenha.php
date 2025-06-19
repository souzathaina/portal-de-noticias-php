<?php
require_once 'includes/conexao.php';

$mensagem = '';
$mensagemCor = 'red'; // Por padrão, assume erro

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Captura os dados enviados pelo formulário
    $email = trim($_POST['email']);
    $usuario = trim($_POST['usuario']);
    $novaSenha = trim($_POST['nova_senha']);

    // Verifica se o e-mail e usuário existem no banco
    $sql = "SELECT id FROM usuarios WHERE email = :email AND nome = :usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();

    $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuarioEncontrado) {
        // Se encontrou, atualiza a senha com hash
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $sqlUpdate = "UPDATE usuarios SET senha = :senha WHERE id = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':senha', $senhaHash);
        $stmtUpdate->bindParam(':id', $usuarioEncontrado['id']);

        if ($stmtUpdate->execute()) {
            $mensagem = "Senha atualizada com sucesso! <a href='login.php'>Fazer login</a>";
            $mensagemCor = 'green'; // sucesso
        } else {
            $mensagem = "Erro ao atualizar a senha. Tente novamente.";
        }
    } else {
        $mensagem = "E-mail ou nome de usuário inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/style_altSenha.css">
    <title>Recuperar Senha</title>
</head>

<body>
    <div class="container">
        <h2>Recuperar Senha</h2>

        <?php if (!empty($mensagem)): ?>
            <p style="color: <?= $mensagemCor ?>;"><?= $mensagem ?></p>
        <?php endif; ?>

        <form action="alterarSenha.php" method="POST">
            <label>Nome de Usuário:</label>
            <input type="text" name="usuario" required>

            <label>E-mail:</label>
            <input type="email" name="email" required>

            <label>Nova Senha:</label>
            <input type="password" name="nova_senha" required>

            <button type="submit">Atualizar Senha</button>
            <a href="login.php">Fazer login</a>
            <a href="index.php">Voltar</a>
        </form>
    </div>
</body>

</html>