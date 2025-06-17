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

    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $foto = isset($_FILES['foto']) ? $_FILES['foto'] : null;

    // Criptografa a senha antes de salvar no banco 
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    //$caminhoFoto = null;

    // Verifica se nome ou email já estão cadastrados no banco
    if (usuarioOuEmailExiste($pdo, $nome, $email)) {
        $mensagem = "Erro: nome de usuário ou e-mail já estão cadastrados.";
    } else {

        // Verifica se o upload da imagem foi realizado sem erro
        // if ($foto && $foto['error'] === 0) {
        //     $pasta = 'imagens/'; // Pasta onde a imagem será salva
        //     // Gera um nome único para o arquivo usando uniqid() para evitar sobrescrita
        //     $nomeUnico = uniqid() . '-' . basename($foto['name']);
        //     $caminhoCompleto = $pasta . $nomeUnico;

        //     // Move a imagem da pasta temporária para a pasta /imagens
        //     if (move_uploaded_file($foto['tmp_name'], $caminhoCompleto)) {
        //         // Salva o caminho da imagem no banco de dados
        //         $caminhoFoto = $caminhoCompleto;
        //     } else {
        //         $mensagem = "Erro ao salvar a imagem.";
        //     }

        // Se não houve erro com a imagem, insere os dados no banco de dados
        // if (!$mensagem) {
        $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql); // Prepara a query com parâmetros

        try {
            // Executa a query com os dados coletados do formulário
            $stmt->execute([$nome, $email, $senhaHash]);

            // Redireciona o usuário para a página de login após cadastro
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            // Mostra erro caso ocorra problema ao salvar no banco
            $mensagem = "Erro ao cadastrar: " . $e->getMessage();
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

    <!-- Mostra mensagem de erro (se houver) -->
    <?php if ($mensagem): ?>
        <p style="color: red;"><?= $mensagem ?></p>
    <?php endif; ?>

    <form action="cadastro.php" method="post" enctype="multipart/form-data">

        <input type="file" name="foto" accept="image/*"><br><br>
        <label>Foto de Perfil:</label><br>


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