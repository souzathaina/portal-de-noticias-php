<?php
// Inclui a conexão com o banco
require_once 'includes/conexao.php';

// Pega todas as notícias do banco, juntando com o nome do autor (usuario)
$sql = "SELECT noticias.id, noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor
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
</head>

<body>

  <header>
    <h1>Portal de Notícias</h1>
  </header>

  <main>
    <?php if (count($noticias) == 0): ?>
      <p>Nenhuma notícia publicada ainda.</p>

      <a href="login.php">Login/cadastro</a>

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
        </article>
      <?php endforeach; ?>
      <a href="login.php">Login/cadastro</a>
    <?php endif; ?>
  </main>

</body>

</html>