<?php
require_once '../includes/conexao.php';

$id = $_GET['id'];
$sql = "SELECT * FROM anuncio WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$anuncio = $stmt->fetch();

if (!$anuncio) {
  echo "Anunciante não encontrado.";
  exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nome = $_POST['nome'];
  $link = $_POST['link'];
  $ativo = isset($_POST['ativo']) ? 1 : 0;
  $destaque = isset($_POST['destaque']) ? 1 : 0;
  $valor = $_POST['valorAnuncio'];

  $imagemNome = $anuncio['imagem']; // Mantém a imagem antiga

  if (!empty($_FILES['imagem']['name'])) {
    $imagem = $_FILES['imagem'];
    $imagemNome = uniqid() . '_' . basename($imagem['name']);
    $caminho = '../imagens/' . $imagemNome;

    if (!move_uploaded_file($imagem['tmp_name'], $caminho)) {
      die("Erro ao salvar a imagem.");
    }

    unlink('../imagens/' . $anuncio['imagem']);
  }

  $sql = "UPDATE anuncio SET nome=?, imagem=?, link=?, ativo=?, destaque=?, valorAnuncio=? WHERE id=?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$nome, $imagemNome, $link, $ativo, $destaque, $valor, $id]);

  header("Location: listarAnuncios.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="../styles/style_cadAnuncio.css">
  <title>Editar Anunciante</title>
</head>

<body>
  <header>
    <img src="../imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
    <div class="menu-toggle" id="menu-toggle">&#9776;</div>
    <nav class="menu" id="menu">
      <a href="listarAnuncios.php">Voltar</a>
      <a href="../telaLogado.php">Inicio</a>
    </nav>
  </header>

  <main>
    <section class="form-container">
      <h2>Editar Anunciante</h2>
      <form method="post" enctype="multipart/form-data" class="form-anunciante">
        <label>Nome:</label>
        <input type="text" name="nome" required value="<?= htmlspecialchars($anuncio['nome']) ?>">

        <label>Imagem atual:</label><br>
        <?php if ($anuncio['imagem']): ?>
          <img src="../imagens/<?= htmlspecialchars($anuncio['imagem']) ?>" alt="Imagem atual"
            style="max-width: 150px;"><br>
        <?php else: ?>
          <small>(sem imagem)</small><br>
        <?php endif; ?>

        <label>Alterar imagem (arquivo):</label>
        <input type="file" name="imagem">

        <label>Link:</label>
        <input type="text" name="link" value="<?= htmlspecialchars($anuncio['link']) ?>">

        <label>Valor (R$):</label>
        <input type="number" step="0.01" name="valorAnuncio" value="<?= htmlspecialchars($anuncio['valorAnuncio']) ?>">

        <label><input type="checkbox" name="ativo" <?= $anuncio['ativo'] ? 'checked' : '' ?>> Ativo</label>
        <label><input type="checkbox" name="destaque" <?= $anuncio['destaque'] ? 'checked' : '' ?>> Destaque</label>

        <div class="botoes">
          <button type="submit">Atualizar</button>
          <a href="listarAnuncios.php" class="btn-voltar">Voltar</a>
        </div>
      </form>

    </section>
  </main>

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
          <img src="../imagens/icons/facebook.png" alt="Facebook">
        </a>
        <a href="https://instagram.com/luzeverdade.portal" target="_blank">
          <img src="../imagens/icons/instagram.png" alt="Instagram">
        </a>
        <a href="https://wa.me/5511999999999" target="_blank">
          <img src="../imagens/icons/whatsapp.png" alt="WhatsApp">
        </a>
      </div>
    </div>
    <div class="copyright">
      <p>&copy; <?= date("Y") ?> Portal Luz & Verdade - Todos os direitos reservados.</p>
    </div>
  </footer>

  <script>
    document.getElementById('menu-toggle').addEventListener('click', function () {
      document.getElementById('menu').classList.toggle('show');
    });
  </script>
</body>

</html>