<?php
session_start();
require_once 'includes/funcoes.php';

//  Garante que só o usuário logado possa acessar
if (!usuarioLogado()) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Confirmar Exclusão</title>
</head>

<body>
    <h1>Confirmar Exclusão de Conta</h1>
    <p style="color: red;">Tem certeza que deseja excluir sua conta? Esta ação é irreversível e todas as suas notícias
        serão apagadas.</p>

    <form method="post" action="excluirUsuario.php" style="display: inline;">
        <button type="submit">✅ Sim, excluir</button>
    </form>

    <form method="get" action="editarUsuario.php" style="display: inline;">
        <button type="submit">❌ Cancelar</button>
    </form>
</body>

</html>