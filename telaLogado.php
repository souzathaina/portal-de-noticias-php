<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// Verifica se o usuário está logado. Se não, redireciona para a página de cadastro.
if (!usuarioLogado()) {
    header("location: cadastro.php");
    exit;
}

// Variável para verificar se o usuário é ADMIN.
$isAdmin = ($_SESSION['id_perfil'] ?? '') === 'ADMIN';

// --- Buscar anúncios ativos e destacados (até 8) ---
$anunciosParaExibir = [];
$quantidadeAnunciosDesejada = 8;

try {
    $sqlDestaques = "SELECT nome, imagem, link FROM anuncio WHERE ativo = 1 AND destaque = 1 ORDER BY RAND() LIMIT $quantidadeAnunciosDesejada";
    $stmtDestaques = $pdo->query($sqlDestaques);
    $anunciosDestaque = $stmtDestaques->fetchAll(PDO::FETCH_ASSOC);

    foreach ($anunciosDestaque as $anuncio) {
        $anunciosParaExibir[] = $anuncio;
    }

    if (count($anunciosParaExibir) < $quantidadeAnunciosDesejada) {
        $sqlAtivos = "SELECT nome, imagem, link FROM anuncio WHERE ativo = 1 ORDER BY RAND()";
        $stmtAtivos = $pdo->query($sqlAtivos);
        $anunciosAtivos = $stmtAtivos->fetchAll(PDO::FETCH_ASSOC);

        $imagensExibidas = array_column($anunciosParaExibir, 'imagem');
        $anunciosAtivosFiltrados = array_filter($anunciosAtivos, function ($anuncio) use ($imagensExibidas) {
            return !in_array($anuncio['imagem'], $imagensExibidas);
        });

        foreach ($anunciosAtivosFiltrados as $anuncio) {
            if (count($anunciosParaExibir) < $quantidadeAnunciosDesejada) {
                $anunciosParaExibir[] = $anuncio;
            } else {
                break;
            }
        }
    }

    while (count($anunciosParaExibir) < $quantidadeAnunciosDesejada) {
        $anunciosParaExibir[] = null;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar anúncios: " . $e->getMessage());
    $anunciosParaExibir = array_fill(0, $quantidadeAnunciosDesejada, null);
}

// --- Buscar todas as notícias com autor ---
$sql = "SELECT noticias.id, noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor, usuarios.id AS id_autor
        FROM noticias
        JOIN usuarios ON noticias.autor = usuarios.id
        ORDER BY noticias.data DESC";
$stmt = $pdo->query($sql);
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Intercalar notícias e anúncios (usar os 4 primeiros anúncios para intercalação) ---
$conteudoCombinado = [];
$indiceAnuncioIntercalado = 0;
$anunciosParaIntercalar = array_slice($anunciosParaExibir, 0, 4);

for ($i = 0; $i < count($noticias); $i++) {
    $conteudoCombinado[] = [
        'tipo' => 'noticia',
        'dados' => $noticias[$i]
    ];

    if (($i + 1) % 2 === 0 && $indiceAnuncioIntercalado < count($anunciosParaIntercalar)) {
        if (!empty($anunciosParaIntercalar[$indiceAnuncioIntercalado])) {
            $conteudoCombinado[] = [
                'tipo' => 'anuncio',
                'dados' => $anunciosParaIntercalar[$indiceAnuncioIntercalado]
            ];
        }
        $indiceAnuncioIntercalado++;
    }
}

// Anúncios restantes (4 ao 7) para exibir no final
$anunciosRestantes = array_slice($anunciosParaExibir, 4);

// --- Tema (dark mode) ---
$themeClass = '';
if (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') {
    $themeClass = 'dark-mode';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="styles/style_telaLogado.css"> <!-- Reutiliza o CSS do index -->
    <title>Portal de Notícias - Área Logada</title>
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="<?= $themeClass ?>">
    <header>
        <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">

        <div class="menu-toggle" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>

        <div class="usuario-area">
            <div class="perfil-usuario">
                <?php
                $fotoUsuario = !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'imagens/perfil_padrao.png';
                ?>
                <img src="<?= htmlspecialchars($fotoUsuario) ?>" alt="Foto do perfil">
                <p><?= htmlspecialchars($_SESSION['nome']) ?></p>
            </div>
            <button id="theme-toggle" class="theme-toggle-button">
                <i class="fas fa-sun icon-light-mode"></i>
                <i class="fas fa-moon icon-dark-mode"></i>
            </button>

            <nav class="menu" id="menu">
                <a href="cadastrarNoticia.php">Criar notícia</a>
                <a href="editarUsuario.php">Editar Usuário</a>
                <?php if ($isAdmin): ?>
                    <a href="./anuncio/listarAnuncios.php">Listar Anúncios</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="conteudo-principal-intercalado">
            <?php if (empty($conteudoCombinado) && empty($anunciosRestantes)): ?>
                <p class="mensagem-vazia">Nenhuma notícia ou anúncio disponível no momento.</p>
            <?php else: ?>
                <?php foreach ($conteudoCombinado as $item): ?>
                    <?php if ($item['tipo'] === 'noticia'): ?>
                        <article class="noticia" data-id="<?= htmlspecialchars($item['dados']['id']) ?>">
                            <a href="noticia.php?id=<?= htmlspecialchars($item['dados']['id']) ?>" class="noticia-link-conteudo">
                                <h2><?= htmlspecialchars($item['dados']['titulo']) ?></h2>
                                <p class="autor-data"><small>Por <?= htmlspecialchars($item['dados']['autor']) ?> em
                                        <?= date('d/m/Y H:i', strtotime($item['dados']['data'])) ?></small></p>

                                <?php if (!empty($item['dados']['imagem'])): ?>
                                    <img src="imagens/<?= htmlspecialchars($item['dados']['imagem']) ?>"
                                        alt="Imagem da notícia: <?= htmlspecialchars($item['dados']['titulo']) ?>">
                                <?php endif; ?>

                                <p>
                                    <?= nl2br(htmlspecialchars(substr($item['dados']['noticia'], 0, 250))) ?>...
                                    <span class="leia-mais">Leia mais</span>
                                </p>
                            </a>
                            <p class="acoes-noticia">
                                <?php 
                                $isAuthor = ($item['dados']['id_autor'] == $_SESSION['id']);
                                if ($isAuthor): ?>
                                    <a href="alterarNoticia.php?id=<?= htmlspecialchars($item['dados']['id']) ?>" class="btn-alterar">Alterar</a>
                                    <a href="excluirNoticia.php?id=<?= htmlspecialchars($item['dados']['id']) ?>" class="btn-excluir">Excluir</a>
                                <?php elseif ($isAdmin): ?>
                                    <a href="excluirNoticia.php?id=<?= htmlspecialchars($item['dados']['id']) ?>" class="btn-excluir">Excluir</a>
                                <?php endif; ?>
                            </p>
                        </article>
                    <?php elseif ($item['tipo'] === 'anuncio'): ?>
                        <div class="anuncio-intercalado">
                            <?php if ($item['dados']): ?>
                                <a href="<?= htmlspecialchars($item['dados']['link']) ?>" target="_blank" title="<?= htmlspecialchars($item['dados']['nome']) ?>">
                                    <img src="imagens/<?= htmlspecialchars($item['dados']['imagem']) ?>"
                                        alt="Anúncio: <?= htmlspecialchars($item['dados']['nome']) ?>">
                                </a>
                            <?php else: ?>
                                <img src="./imagens/anuncio_placeholder_intercalado.png" alt="Seu anúncio intercalado aqui!">
                                <p>Seu anúncio intercalado aqui!</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($anunciosRestantes) && count(array_filter($anunciosRestantes)) > 0): ?>
            <div class="anuncios-no-final">
                <h3>Outros Anúncios</h3>
                <div class="anuncios-grid-final">
                    <?php foreach($anunciosRestantes as $anuncio): ?>
                        <?php if ($anuncio): ?>
                            <div class="anuncio-banner-final">
                                <a href="<?= htmlspecialchars($anuncio['link']) ?>" target="_blank" title="<?= htmlspecialchars($anuncio['nome']) ?>">
                                    <img src="imagens/<?= htmlspecialchars($anuncio['imagem']) ?>" alt="Anúncio: <?= htmlspecialchars($anuncio['nome']) ?>">
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
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
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('menu').classList.toggle('show');
        });

        document.querySelectorAll('article.noticia').forEach(card => {
            card.addEventListener('click', (event) => {
                if (event.target.closest('.acoes-noticia')) return;
                const id = card.getAttribute('data-id');
                if (id) {
                    window.location.href = `noticia.php?id=${id}`;
                }
            });
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    card.querySelector('.noticia-link-conteudo').click();
                }
            });
        });
    </script>
</body>

</html>
