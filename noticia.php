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
  <div class="container">

    <header>
      <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
      <div class="header-texto">
        <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
        <p><small>Por <?= htmlspecialchars($noticia['autor']) ?> em
            <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>
      </div>
      <?php
      if (usuarioLogado()) {
        echo '<a href="telaLogado.php" class="botao-voltar">⬅ Voltar para o início</a>';
      } else {
        echo '<a href="index.php" class="botao-voltar">⬅ Voltar para o início</a>';
      }
      ?>
    </header>


    <aside class="esquerda"> <img src="./imagens/perfil_padrao.png" alt=""></aside>

    <main>


      <section class="noticia">
        <?php if (!empty($noticia['imagem'])): ?>
          <img src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>?v=<?= time() ?>"
            alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>" loading="lazy" />
        <?php endif; ?>

        <p><?= nl2br(htmlspecialchars($noticia['noticia'])) ?></p>
      </section>

    </main>
    <aside class="direita"> <img src="./imagens/perfil_padrao.png" alt=""></aside>
  </div>

  <footer class="rodape-completo">
    <div class="rodape-conteudo">
      <div class="contato">
        <h3>Fale Conosco</h3>
        <p>Email: <a href="mailto:sac@luzeverdade.com">sac@luzeverdade.com</a></p>
        <p>Telefone: <a href="tel:+5511999999999">(11) 99999-9999</a></p>
      </div>

      <div class="redes-sociais">
        <h3>Redes Sociais</h3>
        <a href="https://facebook.com/luzeverdadeoficial" target="_blank">
          <img src="imagens/icons/facebook.png" alt="Facebook">
        </a>
        <a href="https://instagram.com/luzeverdade.portal" target="_blank">
          <img src="imagens/icons/instagram.png" alt="Instagram">
        </a>
        <a href="https://wa.me/5511999999999" target="_blank">
          <img src="imagens/icons/whatsapp.png" alt="WhatsApp">
        </a>
      </div>
    </div>

    <div class="copyright">
      <p>&copy; <?= date("Y") ?> Portal Luz & Verdade - Todos os direitos reservados.</p>
    </div>
  </footer>


</body>

</html>