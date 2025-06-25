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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>
    <link rel="stylesheet" href="styles/style_login.css">
</head>

<body>
    <div class="wrapper">
        <header>
            <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
            <a class="voltar-index" href="index.php">← Voltar ao início</a>
        </header>

        <main>
            <div class="main-container">
                <h2>Login</h2>

                <?php if (!empty($mensagem)): ?>
                    <p class="mensagem-erro"><?= htmlspecialchars($mensagem) ?></p>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <label>Email:</label>
                    <input type="email" name="email" required>

                    <label>Senha:</label>
                    <input type="password" name="senha" required>

                    <button type="submit">Entrar</button>

                    <div class="links">
                        <a href="cadastro.php">Cadastrar-se</a>
                        <a href="alterarSenha.php">Esqueceu a senha?</a>
                    </div>
                </form>
            </div>
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