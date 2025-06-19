<?php

session_start();
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
        ORDER BY noticias.data DESC";
$stmt = $pdo->query($sql);
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="styles/style_telaLogado.css">
    <title>Portal de Notícias</title>

</head>

<body>
    <header>
        <h1>Portal de Notícias</h1>

        <div class="perfil-usuario">
            <?php
            $fotoUsuario = !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'imagens/perfil_padrao.png';
            ?>

            <img src="<?= htmlspecialchars($fotoUsuario) ?>" alt="Foto do perfil">
            <p><?= htmlspecialchars($_SESSION['nome']) ?></p>

        </div>

        <div class="menu">
            <a href="cadastrarNoticia.php">Criar notícia</a>
            <a href="editarUsuario.php">Editar Usuário</a>
            <a href="index.php">Logout</a>
        </div>
    </header>

    <main>
        <?php if (count($noticias) == 0): ?>
            <p>Nenhuma notícia publicada ainda.</p>
        <?php else: ?>
            <div class="noticias-grid">
            <?php foreach ($noticias as $noticia): ?>
                <article class="noticia">
                    <h2>
                        <a href="noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>">
                            <?= htmlspecialchars($noticia['titulo']) ?>
                        </a>
                    </h2>
                    <p><small>Por <?= htmlspecialchars($noticia['autor']) ?> em
                            <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>

                    <?php if (!empty($noticia['imagem'])): ?>
                        <img src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>"
                            alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>"
                            style="max-width: 100%; height: auto;">
                    <?php endif; ?>

                    <p>
                        <?= nl2br(htmlspecialchars(substr($noticia['noticia'], 0, 250))) ?>...
                        <a href="noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>">Leia mais</a>
                    </p>

                    <?php if ($noticia['id_autor'] == $_SESSION['id']): ?>
    <p class="acoes-noticia">
        <a href="alterarNoticia.php?id=<?= htmlspecialchars($noticia['id']) ?>">Alterar</a> |
        <a href="excluirNoticia.php?id=<?= htmlspecialchars($noticia['id']) ?>">Excluir</a>
    </p>
<?php endif; ?>

                </article>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>