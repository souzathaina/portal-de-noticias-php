<?php
require_once '../includes/conexao.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nome = $_POST['nome'];
  $imagem = $_POST['imagem'];
  $link = $_POST['link'];
  $ativo = isset($_POST['ativo']) ? 1 : 0;
  $destaque = isset($_POST['destaque']) ? 1 : 0;
  $valor = $_POST['valorAnuncio'];

  $sql = "INSERT INTO anuncio (nome, imagem, link, ativo, destaque, valorAnuncio)
          VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$nome, $imagem, $link, $ativo, $destaque, $valor]);

  header("Location: listarAnuncios.php");
  exit;
}
?>

<h2>Cadastrar Anunciante</h2>
<form method="post">
  Nome: <input type="text" name="nome" required><br>
  URL da Imagem: <input type="text" name="imagem"><br>
  Link: <input type="text" name="link"><br>
  Valor (R$): <input type="number" step="0.01" name="valorAnuncio"><br>
  Ativo: <input type="checkbox" name="ativo" checked><br>
  Destaque: <input type="checkbox" name="destaque"><br>
  <button type="submit">Salvar</button>
</form>
<a href="listarAnuncios.php">Voltar</a>
