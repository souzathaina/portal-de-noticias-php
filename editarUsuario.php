<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// ✅ Verifica se o usuário está logado
if (!usuarioLogado()) {
    header("Location: login.php");
    exit;
}

$usuarioId = $_SESSION['id'];

// ✅ Busca os dados atuais do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $usuarioId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado.";
    exit;
}

// ✅ Se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Validação simples
    if (empty($nome) || empty($email)) {
        $erro = "Nome e e-mail são obrigatórios.";
    } else {
        // Atualiza nome e email
        $params = [
            'nome' => $nome,
            'email' => $email,
            'id' => $usuarioId
        ];

        $query = "UPDATE usuarios SET nome = :nome, email = :email";

        // Se a senha foi preenchida, atualiza também
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $query .= ", senha = :senha";
            $params['senha'] = $senhaHash;
        }

        $query .= " WHERE id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $_SESSION['nome'] = $nome; // atualiza nome na sessão também

        $mensagem = "Dados atualizados com sucesso!";
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

    <form method="post">
        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required><br><br>

        <label>Nova senha (opcional):</label><br>
        <input type="password" name="senha"><br><br>

        <button type="submit">Salvar</button>
        <a href="dashboard.php">Cancelar</a>
    </form>

    <form action="confirmarExclusaoUsuario.php" method="post"
        onsubmit="return confirm('Tem certeza que deseja excluir sua conta?');">
        <button type="submit" style="color: red; margin-top: 20px;">Excluir Conta</button>
    </form>
</body>

</html>