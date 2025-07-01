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
  $imagem = $_POST['imagem'];
  $link = $_POST['link'];
  $ativo = isset($_POST['ativo']) ? 1 : 0;
  $destaque = isset($_POST['destaque']) ? 1 : 0;
  $valor = $_POST['valorAnuncio'];

  $sql = "UPDATE anuncio SET nome=?, imagem=?, link=?, ativo=?, destaque=?, valorAnuncio=? WHERE id=?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$nome, $imagem, $link, $ativo, $destaque, $valor, $id]);

  header("Location: listarAnuncios.php");
  exit;
}
?>

<h2>Editar Anunciante</h2>
<form method="post">
  Nome: <input type="text" name="nome" value="<?= htmlspecialchars($anuncio['nome']) ?>" required><br>
  URL da Imagem: <input type="text" name="imagem" value="<?= htmlspecialchars($anuncio['imagem']) ?>"><br>
  Link: <input type="text" name="link" value="<?= htmlspecialchars($anuncio['link']) ?>"><br>
  Valor (R$): <input type="number" step="0.01" name="valorAnuncio" value="<?= htmlspecialchars($anuncio['valorAnuncio']) ?>"><br>
  Ativo: <input type="checkbox" name="ativo" <?= $anuncio['ativo'] ? 'checked' : '' ?>><br>
  Destaque: <input type="checkbox" name="destaque" <?= $anuncio['destaque'] ? 'checked' : '' ?>><br>
  <button type="submit">Atualizar</button>
</form>
<a href="listarAnuncios.php">Voltar</a>
