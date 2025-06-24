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
  <link rel="stylesheet" href="styles/style_index.css">
  <title>Portal de Notícias</title>
</head>

<body>
  <header>
  <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">

  <div class="menu-toggle" id="menu-toggle">&#9776;</div> <!-- Ícone ☰ -->

  <nav class="menu" id="menu">
    <a href="index.php">Início</a>
    <a href="login.php">Login / Cadastro</a>
  </nav>
</header>


  <main>
    <?php if (count($noticias) == 0): ?>
      <p class="mensagem-vazia">Nenhuma notícia publicada ainda.</p>
    <?php else: ?>
      <div class="noticias-grid">
        <?php foreach ($noticias as $index => $noticia): ?>
  <?php
    $area = '';
    if ($index === 0) $area = 'item1';
    if ($index === 1) $area = 'item2';
    if ($index === 2) $area = 'item3';
  ?>
  <a href="noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>" class="noticia-link">
    <article class="noticia <?= $area ?>">
      <h2><?= htmlspecialchars($noticia['titulo']) ?></h2>
      <p class="autor-data"><small>Por <?= htmlspecialchars($noticia['autor']) ?> em <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>

      <?php if (!empty($noticia['imagem'])): ?>
        <img src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>" alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>">
      <?php endif; ?>

      <p>
        <?= nl2br(htmlspecialchars(substr($noticia['noticia'], 0, 250))) ?>...
        <span class="leia-mais">Leia mais</span>
      </p>
    </article>
  </a>
<?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <footer>
    <p>&copy; <?= date("Y") ?> Portal Luz & Verdade - Todos os direitos reservados.</p>
  </footer>
  <script>
  document.getElementById('menu-toggle').addEventListener('click', function () {
    document.getElementById('menu').classList.toggle('show');
  });
</script>

</body>

</html>