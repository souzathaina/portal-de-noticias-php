<?php
require_once '../includes/conexao.php';

$sql = "SELECT * FROM anuncio ORDER BY destaque DESC, data_cadastro DESC";
$anuncios = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gerenciar Anunciantes</title>
  <link rel="stylesheet" href="styles/style_index.css">
</head>
<body>
  <h1>Lista de Anunciantes</h1>
  <a href="cadastrarAnuncio.php">+ Novo Anunciante</a>
  <table border="1" cellpadding="8">
    <tr>
      <th>ID</th>
      <th>Nome</th>
      <th>Imagem</th>
      <th>Link</th>
      <th>Destaque</th>
      <th>Ativo</th>
      <th>Ações</th>
    </tr>
    <?php foreach ($anuncios as $anuncio): ?>
    <tr>
      <td><?= $anuncio['id'] ?></td>
      <td><?= htmlspecialchars($anuncio['nome']) ?></td>
      <td><img src="<?= htmlspecialchars($anuncio['imagem']) ?>" width="100"></td>
      <td><a href="<?= htmlspecialchars($anuncio['link']) ?>" target="_blank">Visitar</a></td>
      <td><?= $anuncio['destaque'] ? 'Sim' : 'Não' ?></td>
      <td><?= $anuncio['ativo'] ? 'Sim' : 'Não' ?></td>
      <td>
        <a href="editarAnuncio.php?id=<?= $anuncio['id'] ?>">Editar</a> |
        <a href="excluirAnuncio.php?id=<?= $anuncio['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
