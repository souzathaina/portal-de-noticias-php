<?php

require_once 'includes/conexao.php';

$mensagem = "";

// Função que verifica se já existe nome de usuário ou e-mail no banco
function usuarioOuEmailExiste($pdo, $nome, $email)
{
    $sql = "SELECT 1 FROM usuarios WHERE nome = :nome OR email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $foto = $_FILES['foto'] ?? null;

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $caminhoFoto = null;

    if (usuarioOuEmailExiste($pdo, $nome, $email)) {
        $mensagem = "Erro: nome de usuário ou e-mail já estão cadastrados.";
    } else {
        // Upload da imagem
        if ($foto && $foto['error'] === 0) {
            $pasta = 'imagens/';
            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }

            $nomeUnico = uniqid() . '-' . basename($foto['name']);
            $caminhoCompleto = $pasta . $nomeUnico;

            if (move_uploaded_file($foto['tmp_name'], $caminhoCompleto)) {
                $caminhoFoto = $caminhoCompleto;
            } else {
                $mensagem = "Erro ao salvar a imagem.";
            }
        }

        if (!$mensagem) {
            // Insere o usuário incluindo o caminho da foto (pode ser null)
            $sql = "INSERT INTO usuarios (nome, email, senha, foto) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute([$nome, $email, $senhaHash, $caminhoFoto]);
                header("Location: login.php");
                exit;
            } catch (PDOException $e) {
                $mensagem = "Erro ao cadastrar: " . $e->getMessage();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="styles/style_cadastro.css">
</head>

<body>
    <div class="wrapper">
        <header>
            <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
            <a class="voltar-index" href="index.php">← Voltar ao início</a>
        </header>

        <main>
            <h2>Cadastro de Usuário</h2>

            <?php if (!empty($mensagem)): ?>
                <p class="mensagem-erro"><?= htmlspecialchars($mensagem) ?></p>
            <?php endif; ?>

            <form action="cadastro.php" method="post" enctype="multipart/form-data">
                <label>Foto de Perfil:</label>
                <input type="file" name="foto" accept="image/*">

                <label>Nome:</label>
                <input type="text" name="nome" required>

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Senha:</label>
                <input type="password" name="senha" required>

                <button type="submit">Cadastrar</button>

                <div class="links">
                    <a href="login.php">Já tem conta? Fazer login</a>
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