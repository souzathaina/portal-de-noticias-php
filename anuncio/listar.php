<?php
require_once '../includes/conexao.php';

$sql = "SELECT * FROM anuncio ORDER BY data_cadastro DESC";
$resultado = $conn->query($sql);
?>

<h2>Lista de Anúncios</h2>
<a href="cadastrar.php">Novo Anúncio</a>
<table border="1">
    <tr>
        <th>Nome</th>
        <th>Imagem</th>
        <th>Link</th>
        <th>Ativo</th>
        <th>Destaque</th>
        <th>Valor</th>
        <th>Ações</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['nome']) ?></td>
            <td><img src="<?= htmlspecialchars($row['imagem']) ?>" width="100"></td>
            <td><a href="<?= htmlspecialchars($row['link']) ?>" target="_blank">Link</a></td>
            <td><?= $row['ativo'] ? 'Sim' : 'Não' ?></td>
            <td><?= $row['destaque'] ? 'Sim' : 'Não' ?></td>
            <td>R$ <?= number_format($row['valorAnuncio'], 2, ',', '.') ?></td>
            <td>
                <a href="editar.php?id=<?= $row['id'] ?>">Editar</a> |
                <a href="excluir.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>