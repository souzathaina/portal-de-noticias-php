<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// Verifica se o usuário está logado
if (!usuarioLogado()) {
    header("Location: login.php");
    exit;
}

$usuarioId = $_SESSION['id'];

// Busca os dados atuais do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $usuarioId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado.";
    exit;
}

// Se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $novaFoto = $_FILES['foto'] ?? null;

    // Validação simples
    if (empty($nome) || empty($email)) {
        $erro = "Nome e e-mail são obrigatórios.";
    } else {
        $params = [
            'nome' => $nome,
            'email' => $email,
            'id' => $usuarioId
        ];

        $query = "UPDATE usuarios SET nome = :nome, email = :email";

        // Atualiza senha se foi preenchida
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $query .= ", senha = :senha";
            $params['senha'] = $senhaHash;
        }

        // Upload da nova foto
        if ($novaFoto && $novaFoto['error'] === 0) {
            $pasta = 'imagens/';
            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }

            $nomeUnico = uniqid() . '-' . basename($novaFoto['name']);
            $caminhoCompleto = $pasta . $nomeUnico;

            if (move_uploaded_file($novaFoto['tmp_name'], $caminhoCompleto)) {
                // Remove a imagem antiga, se não for a padrão
                if (!empty($usuario['foto']) && $usuario['foto'] !== 'imagens/perfil_padrao.png') {
                    @unlink($usuario['foto']);
                }

                $query .= ", foto = :foto";
                $params['foto'] = $caminhoCompleto;
                $_SESSION['foto'] = $caminhoCompleto; // atualiza na sessão
            } else {
                $erro = "Erro ao salvar a nova imagem.";
            }
        }

        if (!isset($erro)) {
            $query .= " WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $_SESSION['nome'] = $nome; // atualiza na sessão

            $mensagem = "Dados atualizados com sucesso!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Conta</title>
</head>

<body>
    <h1>Editar Conta</h1>

    <?php if (isset($erro)): ?>
        <p style="color:red;"><?= $erro ?></p>
    <?php elseif (isset($mensagem)): ?>
        <p style="color:green;"><?= $mensagem ?></p>
    <?php endif; ?>

    <?php if (!empty($usuario['foto'])): ?>
        <p>Foto atual:</p>
        <img src="<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto de perfil"
            style="width:100px; height:100px; object-fit:cover; border-radius:50%;">
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Nova foto de perfil (opcional):</label><br>
        <input type="file" name="foto" accept="image/*"><br><br>

        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required><br><br>

        <label>Nova senha (opcional):</label><br>
        <input type="password" name="senha"><br><br>

        <button type="submit">Salvar</button>
        <a href="telaLogado.php">Voltar</a>
    </form>

    <form action="confirmarExclusao.php" method="post"
        onsubmit="return confirm('Tem certeza que deseja excluir sua conta?');">
        <button type="submit" style="color: red; margin-top: 20px;">Excluir Conta</button>
    </form>
</body>

</html>