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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar Notícia</title>
    <link rel="stylesheet" href="styles/style_cadNoticia.css">
</head>

<body>
    <header>
        <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
        <div class="usuario-area">
            <div class="menu-toggle" id="menu-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
            <div class="menu" id="menu">
                <a href="telaLogado.php">Voltar</a>
                <a href="editarUsuario.php">Editar Usuário</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>


    <main>
        <h1>Cadastrar Nova Notícia</h1>

        <form action="" method="POST" enctype="multipart/form-data">
            <label for="titulo">Título:</label>
            <input type="text" name="titulo" id="titulo" required>

            <label for="noticia">Conteúdo:</label>
            <textarea name="noticia" id="noticia" rows="8" required></textarea>

            <label for="imagem">Imagem (opcional):</label>
            <input type="file" name="imagem" id="imagem">

            <button type="submit">Cadastrar Notícia</button>
        </form>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Portal Luz & Verdade - Todos os direitos reservados.</p>
    </footer>

    <script>
        // Quando clicar no ícone de hambúrguer
        document.getElementById('menu-toggle').addEventListener('click', function () {
            const menu = document.getElementById('menu');
            menu.classList.toggle('active'); // Alterna a classe "active" para mostrar/ocultar o menu
        });
    </script>



</body>

</html>