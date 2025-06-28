<?php
require_once '../includes/conexao.php';

$id = $_GET['id'];
$sql = "SELECT * FROM anuncio WHERE id = $id";
$res = $conn->query($sql);
$anuncio = $res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $imagem = $_POST['imagem'];
    $link = $_POST['link'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $valor = $_POST['valorAnuncio'];

    $sql = "UPDATE anuncio SET 
                nome='$nome',
                imagem='$imagem',
                link='$link',
                ativo=$ativo,
                destaque=$destaque,
                valorAnuncio=$valor
            WHERE id = $id";

    $conn->query($sql);
    header("Location: listar.php");
}
?>

<form method="POST">
    <h2>Editar Anúncio</h2>
    Nome: <input type="text" name="nome" value="<?= $anuncio['nome'] ?>" required><br>
    Imagem (URL): <input type="text" name="imagem" value="<?= $anuncio['imagem'] ?>"><br>
    Link: <input type="text" name="link" value="<?= $anuncio['link'] ?>"><br>
    Valor (R$): <input type="number" step="0.01" name="valorAnuncio" value="<?= $anuncio['valorAnuncio'] ?>"><br>
    Ativo: <input type="checkbox" name="ativo" <?= $anuncio['ativo'] ? 'checked' : '' ?>><br>
    Destaque: <input type="checkbox" name="destaque" <?= $anuncio['destaque'] ? 'checked' : '' ?>><br>
    <button type="submit">Salvar</button>
</form>