<?php
require_once 'includes/conexao.php';

// Verifica se o parâmetro 'id' foi passado na URL e se é um número válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Se não tiver um id válido, redireciona para a página inicial
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

$sql = "SELECT noticias.*, usuarios.nome AS autor 
        FROM noticias 
        JOIN usuarios ON noticias.autor = usuarios.id 
        WHERE noticias.id = ?";
$stmt = $pdo->prepare($sql);

// Executa a consulta com o id da notícia
$stmt->execute([$id]);

// Recupera a notícia como um array associativo
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a notícia não for encontrada, redireciona para a página inicial
if (!$noticia) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($noticia['titulo']) ?> - Portal de Notícias</title>
</head>

<body>

    <header>
        <!-- Exibe o título da notícia -->
        <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
        <!-- Exibe o nome do autor e a data formatada -->
        <p><small>Por <?= htmlspecialchars($noticia['autor']) ?> em
                <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>
    </header>

    <main>
        <!-- Se a notícia tiver uma imagem, exibe a imagem -->
        <?php if (!empty($noticia['imagem'])): ?>
            <img src="<?= htmlspecialchars($noticia['imagem']) ?>"
                alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>" />
        <?php endif; ?>

        <!-- Exibe o texto da notícia com quebras de linha convertidas para <br> -->
        <p><?= nl2br(htmlspecialchars($noticia['noticia'])) ?></p>

        <p><a href="index.php">← Voltar para a lista de notícias</a></p>
    </main>

</body>

</html>