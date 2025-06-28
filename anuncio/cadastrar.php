<?php
require_once '../includes/conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $imagem = $_POST['imagem'];
    $link = $_POST['link'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $valor = $_POST['valorAnuncio'];

    $sql = "INSERT INTO anuncio (nome, imagem, link, ativo, destaque, valorAnuncio)
            VALUES ('$nome', '$imagem', '$link', $ativo, $destaque, $valor)";
    $conn->query($sql);

    header("Location: listar.php");
}
?>

<form method="POST">
    <h2>Cadastrar Anúncio</h2>
    Nome: <input type="text" name="nome" required><br>
    Imagem (URL): <input type="text" name="imagem"><br>
    Link: <input type="text" name="link"><br>
    Valor (R$): <input type="number" step="0.01" name="valorAnuncio"><br>
    Ativo: <input type="checkbox" name="ativo" checked><br>
    Destaque: <input type="checkbox" name="destaque"><br>
    <button type="submit">Cadastrar</button>
</form>