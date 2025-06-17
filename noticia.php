<?php
require_once 'includes/conexao.php';

// Verifica se o parâmetro 'id' foi passado via GET e é numérico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Notícia inválida.");
}

$id = (int) $_GET['id'];

// Busca a notícia no banco, juntando o nome do autor
$sql = "SELECT noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor
        FROM noticias
        JOIN usuarios ON noticias.autor = usuarios.id
        WHERE noticias.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$noticia) {
    die("Notícia não encontrada.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($noticia['titulo']) ?></title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <header>
        <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
        <p><small>Por <?= htmlspecialchars($noticia['autor']) ?> em
                <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>
    </header>

    <main>
        <?php if (!empty($noticia['imagem'])): ?>
            <img src="<?= htmlspecialchars($noticia['imagem']) ?>" alt="Imagem da notícia"
                style="max-width: 100%; height: auto;">
        <?php endif; ?>

        <p><?= nl2br(htmlspecialchars($noticia['noticia'])) ?></p>

        <p><a href="index.php">Voltar para o início</a></p>
    </main>
</body>

</html>