<?php
session_start(); // Garante que a sessão seja iniciada para usar $_SESSION['id']
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// ATENÇÃO: Se 'verificaLogin.php' ou uma função como 'usuarioLogado()'
// já inicia a sessão, remova o session_start() acima para evitar warnings.
// Usando 'usuarioLogado()' como na telaLogado.php:
if (!usuarioLogado()) {
    header("location: cadastro.php"); // Redireciona para a página de login/cadastro se não estiver logado
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $noticia = $_POST['noticia'];
    $autor = $_SESSION['id'] ?? null; // ID do autor a partir da sessão, com fallback para null

    // Verificação adicional para garantir que o autor está logado
    if ($autor === null) {
        die("Erro: ID do autor não encontrado na sessão. Por favor, faça login novamente.");
    }

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

        // Redireciona para a página inicial (telaLogado.php) após o cadastro bem-sucedido.
        header("Location: telaLogado.php");
        exit;

    } catch (PDOException $e) {
        // Exibe mensagem de erro caso ocorra alguma falha na inserção.
        echo "Erro ao cadastrar notícia: " . $e->getMessage();
    }
}

// --- Lógica para determinar a classe do tema no carregamento inicial ---
// Isso ajuda a evitar o "flash" de conteúdo sem estilo (FOUC) antes do JS carregar.
$themeClass = '';
if (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') {
    $themeClass = 'dark-mode';
}
// O 'js/theme.js' (que você usa na telaLogado.php) será incluído e aplicará a classe
// 'dark-mode' ao <body> se o tema estiver escuro.
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar Notícia</title>
    <link rel="stylesheet" href="styles/style_cadNoticia.css">
    </head>

<body class="<?= $themeClass ?>">
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
            <button id="theme-toggle" class="theme-toggle-button">
                <span class="icon-light-mode">☀️</span>
                <span class="icon-dark-mode">🌙</span>
            </button>
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
                    <img src="imagens/icons/facebook.png" alt="Facebook">
                </a>
                <a href="https://instagram.com/luzeverdade.portal" target="_blank">
                    <img src="imagens/icons/instagram.png" alt="Instagram">
                </a>
                <a href="https://wa.me/5511999999999" target="_blank">
                    <img src="imagens/icons/whatsapp.png" alt="WhatsApp">
                </a>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; <?= date("Y") ?> Portal Luz & Verdade - Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="js/theme.js"></script>

    <script>
        // Lógica para alternar o menu sanduíche
        document.getElementById('menu-toggle').addEventListener('click', function () {
            const menu = document.getElementById('menu');
            // Use 'show' para consistência com 'telaLogado.php', ou 'active' se o CSS da página usa 'active'
            menu.classList.toggle('active'); // ou 'active', dependendo do seu CSS
        });
    </script>
</body>

</html>