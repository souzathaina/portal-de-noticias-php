<?php
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';
//require_once 'includes/verificaLogin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $noticia = $_POST['noticia'];
    $autor = $_SESSION['id']; // ID do autor a partir da sessão.

    $imagemNome = null;

    // Verifica se uma imagem foi enviada.
    if (!empty($_FILES['imagem']['name'])) {
        $imagem = $_FILES['imagem'];
        $imagemNome = uniqid() . '_' . basename($imagem['name']); // Gera um nome único para a imagem.
        $caminho = 'imagens/' . $imagemNome; // Define o caminho onde a imagem será salva.

        // Move a imagem para o diretório de destino.
        if (!move_uploaded_file($imagem['tmp_name'], $caminho)) {
            die("Erro ao salvar a imagem."); // Interrompe a execução se houver erro ao salvar a imagem.
        }
    }

    try {
        // Prepara a inserção da notícia no banco de dados.
        $sql = "INSERT INTO noticias (titulo, noticia, data, autor, imagem) 
                VALUES (:titulo, :noticia, NOW(), :autor, :imagem)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':noticia' => $noticia,
            ':autor' => $autor,
            ':imagem' => $imagemNome // Insere o nome da imagem (pode ser null se não houver imagem).
        ]);

        // Redireciona para a página inicial após o cadastro bem-sucedido.

        header("Location: telaLogado.php");
        exit;

    } catch (PDOException $e) {
        // Exibe mensagem de erro caso ocorra alguma falha na inserção.
        echo "Erro ao cadastrar notícia: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/style_cadNoticia.css">

    <title>Cadastrar Notícia</title>
</head>

<body>
    <h1>Cadastrar Nova Notícia</h1>

    <form action="" method="POST" enctype="multipart/form-data">
        <label for="titulo">Título:</label><br>
        <input type="text" name="titulo" id="titulo" required><br><br>

        <label for="noticia">Conteúdo:</label><br>
        <textarea name="noticia" id="noticia" rows="8" required></textarea><br><br>

        <label for="imagem">Imagem (opcional):</label><br>
        <input type="file" name="imagem" id="imagem"><br><br>

        <button type="submit">Cadastrar Notícia</button>
        <a href="telaLogado.php">Voltar</a>
    </form>
</body>

</html>