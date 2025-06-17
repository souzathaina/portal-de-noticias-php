<?php
require_once 'includes/conexao.php';

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$foto = $_FILES['foto'];

if ($foto['error'] == 0) {
    $pasta = 'uploads/';
    if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
    }
    $nomeArquivo = uniqid() . '-' . basename($foto['name']);
    $caminhoCompleto = $pasta . $nomeArquivo;

    // Verifica se o email já está cadastrado
    $sqlCheck = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$email]);
    $existe = $stmtCheck->fetchColumn();

    if ($existe) {
        echo "Erro: o email '$email' já está cadastrado.";
        exit;
    }

    if (move_uploaded_file($foto['tmp_name'], $caminhoCompleto)) {
        // Insere os dados no banco
        $sql = "INSERT INTO usuarios (nome, email, foto) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $caminhoCompleto]);

        header("Location: telaLogado.php");
        exit;
    } else {
        echo "Erro ao mover o arquivo.";
    }
} else {
    echo "Erro no upload: " . $foto['error'];
}
?>
