<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

//require_once 'includes/verificaLogin.php';

// Verifica se o usuário está logado
if (!usuarioLogado()) {
    header("location: login.php");
    exit;
}

// Pega o ID da notícia via GET ou POST
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

// Se não tiver ID, redireciona
if ($id <= 0) {
    header("Location: telaLogado.php");
    exit;
}

// Carrega a notícia para verificar se o usuário é o autor
$sql = "SELECT * FROM noticias WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a notícia não existir ou o usuário não for o autor, bloqueia
if (!$noticia || $noticia['autor'] != $_SESSION['id']) {
    echo "Você não tem permissão para excluir esta notícia.";
    exit;
}

// Se o usuário confirmou a exclusão (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && $_POST['confirmar'] === 'sim') {
    try {
        $sql = "DELETE FROM noticias WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Opcional: Se quiser, pode também excluir a imagem do servidor (se quiser te passo como fazer)

        header("Location: telaLogado.php");
        exit;

    } catch (PDOException $e) {
        echo "Erro ao excluir notícia: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/style_excluirNoticia.css">
    <title>Excluir Notícia</title>
</head>

<body>

    <header>
        <div class="container-header">
            <img src="imagens/logo/logo.png" alt="Logo do Site" />
            <nav>
                <a href="telaLogado.php">Início</a> |
                <a href="editarUsuario.php">Editar Perfil</a> |
                <a href="logout.php">Sair</a>
            </nav>
        </div>
    </header>

    <main>
        <h2>Excluir Notícia</h2>
        <p>Tem certeza que deseja excluir a notícia: <strong><?= htmlspecialchars($noticia['titulo']) ?></strong>?</p>

        <form action="" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <button type="submit" name="confirmar" value="sim">✅ Sim, excluir</button>
            <a class="cancelar" href="telaLogado.php">❌ Cancelar</a>
        </form>
    </main>

    <footer>
        <div class="container-footer">
            <p>© <?= date("Y"); ?> Meu Portal de Notícias. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>