<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// Verifica se o usuário está logado
if (!usuarioLogado()) {
    header("location: cadastro.php");
    exit;
}

// --- Lógica para buscar anúncios (AJUSTADA) ---
$anuncioDestaqueEsquerda = null;
$anuncioDestaqueDireita = null;

try {
    // 1. Tenta buscar TODOS os anúncios ativos e em destaque
    $sqlDestaques = "SELECT nome, imagem, link FROM anuncio WHERE ativo = 1 AND destaque = 1 ORDER BY RAND()";
    $stmtDestaques = $pdo->query($sqlDestaques);
    $anunciosDestaque = $stmtDestaques->fetchAll(PDO::FETCH_ASSOC);

    // 2. Tenta buscar TODOS os anúncios ativos (para usar como fallback)
    $sqlAtivos = "SELECT nome, imagem, link FROM anuncio WHERE ativo = 1 ORDER BY RAND()";
    $stmtAtivos = $pdo->query($sqlAtivos);
    $anunciosAtivos = $stmtAtivos->fetchAll(PDO::FETCH_ASSOC);

    // Prioriza destaques
    if (count($anunciosDestaque) >= 2) {
        // Se tem pelo menos 2 destaques, pega 2 diferentes
        $anuncioDestaqueEsquerda = array_shift($anunciosDestaque); // Pega o primeiro
        $anuncioDestaqueDireita = array_shift($anunciosDestaque);  // Pega o segundo
    } elseif (count($anunciosDestaque) == 1) {
        // Se tem apenas 1 destaque, ele vai para a esquerda
        $anuncioDestaqueEsquerda = array_shift($anunciosDestaque);

        // E tenta pegar um ativo diferente para a direita
        foreach ($anunciosAtivos as $anuncio) {
            if ($anuncio['imagem'] !== $anuncioDestaqueEsquerda['imagem']) {
                $anuncioDestaqueDireita = $anuncio;
                break;
            }
        }
    }

    // Se ainda faltar algum (e houver ativos disponíveis que não foram usados)
    if (!$anuncioDestaqueEsquerda) {
        if (!empty($anunciosAtivos)) {
            $anuncioDestaqueEsquerda = array_shift($anunciosAtivos);
        }
    }

    if (!$anuncioDestaqueDireita) {
        foreach ($anunciosAtivos as $anuncio) {
            // Garante que o anúncio da direita seja diferente do da esquerda, se a esquerda já tiver um
            if (!$anuncioDestaqueEsquerda || $anuncio['imagem'] !== $anuncioDestaqueEsquerda['imagem']) {
                $anuncioDestaqueDireita = $anuncio;
                break;
            }
        }
    }


} catch (PDOException $e) {
    error_log("Erro ao buscar anúncios: " . $e->getMessage());
    $anuncioDestaqueEsquerda = null;
    $anuncioDestaqueDireita = null;
}
// --- Fim da lógica para buscar anúncios ---


// Pega todas as notícias do banco, junto com o nome do autor (usuário)
$sql = "SELECT noticias.id, noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor, usuarios.id AS id_autor
        FROM noticias
        JOIN usuarios ON noticias.autor = usuarios.id
        ORDER BY noticias.data DESC";
$stmt = $pdo->query($sql);
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para determinar a classe do tema ---
$themeClass = '';
// Verifica se há um cookie de tema ou uma preferência de sistema
// Esta lógica será mais robusta no JavaScript, mas é bom ter uma base aqui para evitar o "flash"
if (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') {
    $themeClass = 'dark-mode';
}
// O theme.js irá sobrescrever/aplicar isso dinamicamente no cliente
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="styles/style_telaLogado.css">
    <title>Portal de Notícias</title>
</head>

<body class="<?= $themeClass ?>">
    <header>
        <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">

        <div class="menu-toggle" id="menu-toggle">&#9776;</div>

        <div class="usuario-area">
            <div class="perfil-usuario">
                <?php
                // Usa a foto do usuário da sessão, ou uma imagem padrão se não houver
                $fotoUsuario = !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'imagens/perfil_padrao.png';
                ?>
                <img src="<?= htmlspecialchars($fotoUsuario) ?>" alt="Foto do perfil">
                <p><?= htmlspecialchars($_SESSION['nome']) ?></p>
            </div>
            <button id="theme-toggle" class="theme-toggle-button">
                    <span class="icon-light-mode">☀️</span>
                    <span class="icon-dark-mode">🌙</span>
                </button>


            <nav class="menu" id="menu">
                <a href="cadastrarNoticia.php">Criar notícia</a>
                <a href="editarUsuario.php">Editar Usuário</a>
                <a href="logout.php">Logout</a>
                <a href="./anuncio/listarAnuncios.php">Listar Anúncios</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="anuncio-lateral anuncio-esquerda">
            <?php if ($anuncioDestaqueEsquerda): ?>
                <a href="<?= htmlspecialchars($anuncioDestaqueEsquerda['link']) ?>" target="_blank" title="<?= htmlspecialchars($anuncioDestaqueEsquerda['nome']) ?>">
                    <img src="imagens/<?= htmlspecialchars($anuncioDestaqueEsquerda['imagem']) ?>" alt="Anúncio: <?= htmlspecialchars($anuncioDestaqueEsquerda['nome']) ?>">
                    <p><?= htmlspecialchars($anuncioDestaqueEsquerda['nome']) ?></p>
                </a>
            <?php else: ?>
                <img src="./imagens/anuncio_exemplo_esquerda.png" alt="Anúncio Lateral Esquerdo">
                <p>Seu anúncio aqui na Esquerda!</p>
            <?php endif; ?>
        </div>

        <section class="noticias-principal">
            <?php if (count($noticias) == 0): ?>
                <p class="mensagem-vazia">Nenhuma notícia publicada ainda.</p>
            <?php else: ?>
                <div class="noticias-grid">
                    <?php foreach ($noticias as $noticia): ?>
                        <article class="noticia" data-id="<?= htmlspecialchars($noticia['id']) ?>">
                            <a href="noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>" class="noticia-link-conteudo">
                                <h2><?= htmlspecialchars($noticia['titulo']) ?></h2>
                                <p class="autor-data"><small>Por <?= htmlspecialchars($noticia['autor']) ?> em
                                        <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>

                                <?php if (!empty($noticia['imagem'])): ?>
                                    <img src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>"
                                        alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>">
                                <?php endif; ?>

                                <p>
                                    <?= nl2br(htmlspecialchars(substr($noticia['noticia'], 0, 250))) ?>...
                                    <span class="leia-mais">Leia mais</span>
                                </p>
                            </a> <?php if ($noticia['id_autor'] == $_SESSION['id']): ?>
                                <p class="acoes-noticia">
                                    <a href="alterarNoticia.php?id=<?= htmlspecialchars($noticia['id']) ?>"
                                        class="btn-alterar">Alterar</a>
                                    <a href="excluirNoticia.php?id=<?= htmlspecialchars($noticia['id']) ?>"
                                        class="btn-excluir">Excluir</a>
                                </p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <div class="anuncio-lateral anuncio-direita">
            <?php if ($anuncioDestaqueDireita): ?>
                <a href="<?= htmlspecialchars($anuncioDestaqueDireita['link']) ?>" target="_blank" title="<?= htmlspecialchars($anuncioDestaqueDireita['nome']) ?>">
                    <img src="imagens/<?= htmlspecialchars($anuncioDestaqueDireita['imagem']) ?>" alt="Anúncio: <?= htmlspecialchars($anuncioDestaqueDireita['nome']) ?>">
                    <p><?= htmlspecialchars($anuncioDestaqueDireita['nome']) ?></p>
                </a>
            <?php else: ?>
                <img src="./imagens/anuncio_exemplo_direita.png" alt="Anúncio Lateral Direito">
                <p>O seu espaço aqui na Direita!</p>
            <?php endif; ?>
        </div>
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

    <script src="js/theme.js"></script> <script>
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('menu').classList.toggle('show');
        });

        document.querySelectorAll('article.noticia').forEach(card => {
            card.addEventListener('click', () => {
                const id = card.getAttribute('data-id');
                if (id) {
                    window.location.href = `noticia.php?id=${id}`;
                }
            });

            // Acessibilidade: permite "entrar" no link com Enter ou Espaço
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    card.click();
                }
            });
        });
    </script>

</body>

</html>