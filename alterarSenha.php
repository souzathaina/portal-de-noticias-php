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
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="styles/style_altSenha.css">
</head>

<body>
    <div class="wrapper">
        <header>
            <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
            <a class="voltar-index" href="index.php">← Voltar ao início</a>
        </header>

        <main>
            <h2>Recuperar Senha</h2>

            <?php if (!empty($mensagem)): ?>
                <p class="mensagem" style="color: <?= $mensagemCor ?>;"><?= $mensagem ?></p>
            <?php endif; ?>

            <form action="alterarSenha.php" method="POST">
                <label>Nome de Usuário:</label>
                <input type="text" name="usuario" required>

                <label>E-mail:</label>
                <input type="email" name="email" required>

                <label>Nova Senha:</label>
                <input type="password" name="nova_senha" required>

                <button type="submit">Atualizar Senha</button>

                <div class="links">
                    <a href="login.php">Fazer login</a>
                </div>
            </form>
        </main>

        <footer class="rodape-completo">
            <div class="rodape-conteudo">
                <div class="contato">
                    <h3>Fale Conosco</h3>
                    <p>Email: <a href="mailto:sac@luzeverdade.com">sac@luzeverdade.com</a></p>
                    <p>Telefone: <a href="tel:+5511999999999">(11) 99999-9999</a></p>
                </div>

                <div class="redes-sociais">
                    <h3>Redes Sociais</h3>
                    <a href="https://facebook.com/luzeverdadeoficial" target="_blank">
                        <img src="imagens/icons/facebook.png" alt="Facebook">
                    </a>
                    <a href="https://instagram.com/luzeverdade.portal" target="_blank">
                        <img src="imagens/icons/instagram.png" alt="Instagram">
                    </a>
                    <a href="https://wa.me/5511999999999" target="_blank">
                        <img src="imagens/icons/whatsapp.png" alt="WhatsApp">
                    </a>
                </div>
            </div>

            <div class="copyright">
                <p>&copy; <?= date("Y") ?> Portal Luz & Verdade - Todos os direitos reservados.</p>
            </div>
        </footer>

    </div>
</body>

</html>