<?php 
session_start();
// Inclui a conexão com o banco
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

if (!usuarioLogado()) {
    header("location: cadastro.php");
    exit;
}

// Pega todas as notícias do banco, juntando com o nome do autor (usuario)
$sql = "SELECT noticias.id, noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor, usuarios.id AS id_autor
        FROM noticias
        JOIN usuarios ON noticias.autor = usuarios.id
        ORDER BY noticias.data DESC"; // Ordena da mais recente para a mais antiga

$stmt = $pdo->query($sql);
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Portal de Notícias</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>

    <header>
        <h1>Portal de Notícias</h1>
    </header>

    <main>
        <?php if (count($noticias) == 0): ?>

            <p>Nenhuma notícia publicada ainda.</p>

            <div class="menu">
                <a href="cadastrarNoticia.php">Criar notícia</a>
                <a href="logout.php">Logout</a>
            </div>

        <?php else: ?>
            <?php foreach ($noticias as $noticia): ?>

                <article class="noticia">
                    <h2>
                        <!-- Link para página individual, passando o id -->
                        <a href="noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>">
                            <?= htmlspecialchars($noticia['titulo']) ?>
                        </a>
                    </h2>
                    <p><small>Por <?= htmlspecialchars($noticia['autor']) ?> em
                            <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>

                    <?php if (!empty($noticia['imagem'])): ?>
                        <img src="<?= htmlspecialchars($noticia['imagem']) ?>"
                            alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>" />
                    <?php endif; ?>

                    <p>
                        <?= nl2br(htmlspecialchars(substr($noticia['noticia'], 0, 250))) ?>...
                        <a href="noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>">Leia mais</a>
                    </p>

                    <!-- Só exibe o link ALTERAR e EXCLUIR se o usuário for o autor da notícia -->
                    <?php if ($noticia['id_autor'] == $_SESSION['id']): ?>
                        <p>
                            <a href="alterarNoticia.php?id=<?= htmlspecialchars($noticia['id']) ?>">Alterar</a> |
                            <a href="excluirNoticia.php?id=<?= htmlspecialchars($noticia['id']) ?>">Excluir</a>
                        </p>
                    <?php endif; ?>

                </article>

            <?php endforeach; ?>

            <div class="menu">
                <a href="cadastrarNoticia.php">Criar notícia</a>
                <a href="logout.php">Sair</a>
            </div>
        <?php endif; ?>
    </main>

</body>

</html>
