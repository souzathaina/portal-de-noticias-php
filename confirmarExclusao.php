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
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="styles/style_confirmarExclusao.css" />
    <title>Confirmar Exclusão</title>
</head>

<body>

    <header>
        <div class="container-header">
            <img src="imagens/logo/logo.png" alt="Logo do Site" />
            
            <nav>
                <a href="telaLogado.php">Início</a> |
                <a href="editarUsuario.php">Editar Perfil</a> |
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <h2>Confirmar Exclusão de Conta</h2>
        <p style="color: red;">
            Tem certeza que deseja excluir sua conta? Esta ação é irreversível e todas as suas notícias serão apagadas.
        </p>

        <form method="post" action="excluirUsuario.php" style="display: inline;">
            <button type="submit">✅ Sim, excluir</button>
        </form>

        <form method="get" action="editarUsuario.php" style="display: inline;">
            <button type="submit">❌ Cancelar</button>
        </form>
    </main>

    <footer>
        <div class="container-footer">
            <p>© <?= date("Y"); ?> Meu Portal de Notícias. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>