<?php
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($noticia['titulo']) ?></title>
    <link rel="stylesheet" href="styles/style_noticia.css" />
</head>

<body>
  <div class="container"> <!-- Início da estrutura flexível -->

    <header>
      <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
      <div class="header-texto">
        <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
        <p><small>Por <?= htmlspecialchars($noticia['autor']) ?> em <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>
      </div>
      <?php
        if (usuarioLogado()) {
          echo '<a href="telaLogado.php" class="botao-voltar">⬅ Voltar para o início</a>';
        } else {
          echo '<a href="index.php" class="botao-voltar">⬅ Voltar para o início</a>';
        }
      ?>
    </header>

    <main>
      <?php if (!empty($noticia['imagem'])): ?>
        <img 
          src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>?v=<?= time() ?>" 
          alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>"
          loading="lazy"
        />
      <?php endif; ?>

      <p><?= nl2br(htmlspecialchars($noticia['noticia'])) ?></p>

      
    </main>

    <footer>
      <p>&copy; <?= date("Y") ?> Portal Luz & Verdade - Todos os direitos reservados.</p>
    </footer>

  </div> 
</body>

</html>