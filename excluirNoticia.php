<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// Verifica se a função usuarioLogado existe, caso contrário, define uma versão simples.
// Idealmente, funcoes.php deve ser carregado e conter essa função.
if (!function_exists('usuarioLogado')) {
    function usuarioLogado() {
        return isset($_SESSION['id']);
    }
}

if (!usuarioLogado()) {
    header("location: login.php");
    exit;
}

// Pega o ID da notícia via GET ou POST
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

// Se não tiver ID, redireciona
if ($id <= 0) {
    header("Location: telaLogado.php");
    exit;
}

// Carrega a notícia para verificar se o usuário é o autor
$sql = "SELECT * FROM noticias WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a notícia não existir ou o usuário não for o autor, bloqueia
if (!$noticia || $noticia['autor'] != $_SESSION['id']) {
    echo "Você não tem permissão para excluir esta notícia.";
    exit;
}

// Se o usuário confirmou a exclusão (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar']) && $_POST['confirmar'] === 'sim') {
    try {
        // Opcional: Se quiser, pode também excluir a imagem do servidor (se quiser te passo como fazer)
        /*
        if (!empty($noticia['imagem'])) {
            $caminhoImagem = 'caminho/para/suas/imagens/' . $noticia['imagem']; // AJUSTE ESTE CAMINHO
            if (file_exists($caminhoImagem)) {
                unlink($caminhoImagem);
            }
        }
        */

        $sql = "DELETE FROM noticias WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        header("Location: telaLogado.php");
        exit;

    } catch (PDOException $e) {
        echo "Erro ao excluir notícia: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Excluir Notícia</title>
    <link rel="stylesheet" href="styles/style_excluirNoticia.css" />
</head>

<body>
    <header>
        <div class="container-header">
            <img src="imagens/logo/logo.png" alt="Logo do Site" />

            <button class="menu-toggle" aria-label="Abrir menu">
                <span class="hamburger"></span>
                <span class="hamburger"></span>
                <span class="hamburger"></span>
            </button>

            <nav>
                <a href="telaLogado.php">Início</a>
                <a href="editarUsuario.php">Editar Perfil</a>
                <a href="logout.php">Sair</a>
                <button id="theme-toggle" class="theme-toggle-button">
                    <span class="icon-light-mode">☀️</span>
                    <span class="icon-dark-mode">🌙</span>
                </button>
            </nav>
        </div>
    </header>

    <main>
        <h2>Excluir Notícia</h2>
        <p>Tem certeza que deseja excluir a notícia: <strong><?= htmlspecialchars($noticia['titulo']) ?></strong>?</p>

        <form action="" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>" />
            <button type="submit" name="confirmar" value="sim">✅ Sim, excluir</button>
            <a class="cancelar" href="telaLogado.php">❌ Cancelar</a>
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

    <script>
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('header nav');

        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });

        // JavaScript para alternar o tema (dark mode)
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        // Função para aplicar o tema salvo
        function applyTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }
        }

        // Aplica o tema ao carregar a página
        applyTheme();

        // Adiciona evento de clique para alternar o tema
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            // Salva a preferência do usuário
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
</body>

</html>