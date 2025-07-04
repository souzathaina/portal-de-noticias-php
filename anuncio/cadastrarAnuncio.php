<?php
require_once '../includes/conexao.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nome = $_POST['nome'];
  $link = $_POST['link'];
  $ativo = isset($_POST['ativo']) ? 1 : 0;
  $destaque = isset($_POST['destaque']) ? 1 : 0;
  $valor = $_POST['valorAnuncio'];

  $imagemNome = null;

  if (!empty($_FILES['imagem']['name'])) {
    $imagem = $_FILES['imagem'];
    $imagemNome = uniqid() . '_' . basename($imagem['name']);
    $caminho = '../imagens/' . $imagemNome;

    if (!move_uploaded_file($imagem['tmp_name'], $caminho)) {
      die("Erro ao salvar a imagem.");
    }
  }

  $sql = "INSERT INTO anuncio (nome, imagem, link, ativo, destaque, valorAnuncio)
            VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$nome, $imagemNome, $link, $ativo, $destaque, $valor]);

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
  <title>Cadastrar Anunciante</title>
</head>

<body>
  <header>
    <img src="../imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
    <div class="menu-toggle" id="menu-toggle">&#9776;</div>
    <nav class="menu" id="menu">
      <a href="../telaLogado.php">Início</a>
    </nav>
  </header>

  <main>
    <section class="form-container">
      <h2>Cadastrar Anunciante</h2>
      <form method="post" class="form-anunciante" enctype="multipart/form-data"> <label>Nome:</label>
        <input type="text" name="nome" required>

        <label>URL da Imagem:</label>
        <input type="file" name="imagem">

        <label>Link:</label>
        <input type="text" name="link">

        <label>Valor (R$):</label>
        <input type="number" step="0.01" name="valorAnuncio">

        <label><input type="checkbox" name="ativo" checked> Ativo</label>
        <label><input type="checkbox" name="destaque"> Destaque</label>

        <div class="botoes">
          <button type="submit">Salvar</button>
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