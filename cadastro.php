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
</head>

<body>
    <h2>Cadastro de Usuário</h2>

    <?php if ($mensagem): ?>
        <p style="color: red;"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <form action="cadastro.php" method="post" enctype="multipart/form-data">
        <label>Foto de Perfil:</label><br>
        <input type="file" name="foto" accept="image/*"><br><br>

        <label>Nome:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <input type="submit" value="Cadastrar">
    </form>
</body>

</html>
