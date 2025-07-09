<?php
// Inclui a conexão com o banco
require_once 'includes/conexao.php';

// --- Anúncio em destaque para pop-up ---
$sqlPopup = "SELECT * FROM anuncio WHERE ativo = 1 AND destaque = 1 ORDER BY RAND() LIMIT 1";
$stmtPopup = $pdo->query($sqlPopup);
$popupAnuncio = $stmtPopup->fetch(PDO::FETCH_ASSOC);

// Previsão do tempo - OpenWeather
$cidade = "Sapucaia do Sul,BR";
$apiKey = "9c1317cf29a3f077747a2a410f1b5bf8"; // Substitua pela sua chave real se for usar em produção
$url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cidade) . "&appid=$apiKey&units=metric&lang=pt_br";

$tempo = null;
// Faz a requisição com cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // segundos
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ignora certificado SSL

$response = curl_exec($ch);
$erroCurl = curl_error($ch);
curl_close($ch);

if ($response && !$erroCurl) {
    $dados = json_decode($response, true);
    if (isset($dados['main'])) {
        $tempo = [
            'cidade' => $dados['name'],
            'temperatura' => round($dados['main']['temp']),
            'descricao' => ucfirst($dados['weather'][0]['description']),
            'icone' => $dados['weather'][0]['icon']
        ];
    }
}

// --- Notícias para o carrossel e para o conteúdo principal ---
// Pega todas as notícias do banco, juntando com o nome do autor (usuario)
// Buscamos um número maior de notícias para garantir que haja o suficiente após separar para o carrossel.
$sqlTodasNoticias = "SELECT noticias.id, noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor
                    FROM noticias
                    JOIN usuarios ON noticias.autor = usuarios.id
                    ORDER BY noticias.data DESC";
$stmtTodasNoticias = $pdo->query($sqlTodasNoticias);
$todasNoticias = $stmtTodasNoticias->fetchAll(PDO::FETCH_ASSOC);

// Separar Notícias para o Carrossel (Exemplo: 3 últimas)
$ultimasNoticias = array_slice($todasNoticias, 0, 3);
// As notícias restantes serão usadas no conteúdo principal
$noticiasPrincipais = array_slice($todasNoticias, 3);


// --- CÓDIGO PARA PEGAR ANÚNCIOS (AJUSTADO PARA TER ATÉ 8 ANÚNCIOS) ---
$anunciosParaExibir = []; // Array para armazenar os anúncios que serão exibidos
$quantidadeAnunciosDesejada = 8; // Define a quantidade desejada de anúncios

try {
    // Tenta buscar TODOS os anúncios ativos e em destaque (pelo menos 8, se possível)
    $sqlDestaques = "SELECT nome, imagem, link FROM anuncio WHERE ativo = 1 AND destaque = 1 ORDER BY RAND() LIMIT " . $quantidadeAnunciosDesejada;
    $stmtDestaques = $pdo->query($sqlDestaques);
    $anunciosDestaque = $stmtDestaques->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os destaques ao array de anúncios para exibir
    foreach ($anunciosDestaque as $anuncio) {
        $anunciosParaExibir[] = $anuncio;
    }

    // Se não tiver a quantidade desejada de destaques, completa com anúncios ativos não-destaque
    if (count($anunciosParaExibir) < $quantidadeAnunciosDesejada) {
        $sqlAtivos = "SELECT nome, imagem, link FROM anuncio WHERE ativo = 1 ORDER BY RAND()";
        $stmtAtivos = $pdo->query($sqlAtivos);
        $anunciosAtivos = $stmtAtivos->fetchAll(PDO::FETCH_ASSOC);

        // Remove anúncios que já estão nos destaques para evitar duplicidade
        $imagensExibidas = array_column($anunciosParaExibir, 'imagem');
        $anunciosAtivosFiltrados = array_filter($anunciosAtivos, function ($anuncio) use ($imagensExibidas) {
            return !in_array($anuncio['imagem'], $imagensExibidas);
        });

        // Adiciona anúncios ativos até completar a quantidade desejada
        foreach ($anunciosAtivosFiltrados as $anuncio) {
            if (count($anunciosParaExibir) < $quantidadeAnunciosDesejada) {
                $anunciosParaExibir[] = $anuncio;
            } else {
                break; // Já temos a quantidade de anúncios desejada
            }
        }
    }

    // Garante que sempre teremos a quantidade desejada de posições (mesmo que algumas fiquem vazias)
    while (count($anunciosParaExibir) < $quantidadeAnunciosDesejada) {
        $anunciosParaExibir[] = null; // Preenche com nulo se não houver anúncios suficientes
    }

} catch (PDOException $e) {
    error_log("Erro ao buscar anúncios: " . $e->getMessage());
    $anunciosParaExibir = array_fill(0, $quantidadeAnunciosDesejada, null); // Em caso de erro, preenche com nulos
}
// --- Fim da lógica para buscar anúncios ---


// --- NOVA LÓGICA: Intercalar Notícias e Anúncios no conteúdo principal ---
$conteudoCombinado = [];
$indiceAnuncioIntercalado = 0; // Para controlar qual anúncio de intercalação usar

// Pega os 4 primeiros anúncios para a intercalação principal
$anunciosParaIntercalar = array_slice($anunciosParaExibir, 0, 4);

// Itera sobre as notícias principais
for ($i = 0; $i < count($noticiasPrincipais); $i++) {
    // Adiciona a notícia atual
    $conteudoCombinado[] = [
        'tipo' => 'noticia',
        'dados' => $noticiasPrincipais[$i]
    ];

    // Adiciona um anúncio a cada 2 notícias, se houver anúncios disponíveis para intercalar
    if (($i + 1) % 2 === 0 && $indiceAnuncioIntercalado < count($anunciosParaIntercalar)) {
        if (!empty($anunciosParaIntercalar[$indiceAnuncioIntercalado])) { // Garante que o anúncio não é nulo/vazio
            $conteudoCombinado[] = [
                'tipo' => 'anuncio',
                'dados' => $anunciosParaIntercalar[$indiceAnuncioIntercalado]
            ];
        }
        $indiceAnuncioIntercalado++; // Próximo anúncio para a próxima vez
    }
}

// Anúncios restantes (do índice 4 ao 7, se existirem) para exibir no final
$anunciosRestantes = array_slice($anunciosParaExibir, 4);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="styles/style_index.css">
    <title>Portal de Notícias</title>
    <link rel="icon" href="imagens/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php if ($popupAnuncio): ?>
        <div id="popup-anuncio" class="popup">
            <div class="popup-conteudo">
                <span class="fechar" onclick="fecharPopup()">&times;</span>
                <?php if (!empty($popupAnuncio['link'])): ?>
                    <a href="<?= htmlspecialchars($popupAnuncio['link']) ?>" target="_blank">
                    <?php endif; ?>
                    <img src="imagens/<?= htmlspecialchars($popupAnuncio['imagem']) ?>"
                        alt="<?= htmlspecialchars($popupAnuncio['nome']) ?>" class="imagem-anuncio">
                    <?php if (!empty($popupAnuncio['link'])): ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <header>
        <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">

        <div class="menu-toggle" id="menu-toggle">
            <i class="fas fa-bars"></i> </div>

        <div class="tempo-area">
            <?php if ($tempo): ?>
                <div class="tempo-box">
                    <img src="https://openweathermap.org/img/wn/<?= $tempo['icone'] ?>.png" alt="Tempo">
                    <span><?= $tempo['temperatura'] ?>°C</span>
                    <span><?= htmlspecialchars($tempo['descricao']) ?></span>
                </div>
            <?php else: ?>
                <div class="tempo-box erro">
                    <span>Tempo indisponível</span>
                </div>
            <?php endif; ?>

            <nav class="menu" id="menu">
                <a href="index.php">Início</a>
                <a href="login.php">Login / Cadastro</a>
                <button id="theme-toggle" class="theme-toggle-button">
                    <i class="fas fa-sun icon-light-mode"></i>
                    <i class="fas fa-moon icon-dark-mode"></i>
                    Mudar Tema
                </button>
            </nav>
        </div>
    </header>

    <?php if (!empty($ultimasNoticias)): ?>
        <section class="carrossel-container">
            <div class="carrossel" id="carrossel">
                <?php foreach ($ultimasNoticias as $noticia): ?>
                    <a href="noticia.php?id=<?= $noticia['id'] ?>" class="slide">
                        <img src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>"
                            alt="<?= htmlspecialchars($noticia['titulo']) ?>">
                        <div class="titulo-slide"><?= htmlspecialchars($noticia['titulo']) ?></div>
                        <p class="autor-slide">Por <?= htmlspecialchars($noticia['autor']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
            <button class="seta anterior" onclick="mudarSlide(-1)">&#10094;</button>
            <button class="seta proximo" onclick="mudarSlide(1)">&#10095;</button>
        </section>
    <?php endif; ?>

    <main>
        <div class="conteudo-principal-intercalado">
            <?php if (empty($conteudoCombinado) && empty($anunciosRestantes)): ?>
                <p class="mensagem-vazia">Nenhuma notícia ou anúncio disponível no momento.</p>
            <?php else: ?>
                <?php foreach ($conteudoCombinado as $item): ?>
                    <?php if ($item['tipo'] === 'noticia'): ?>
                        <article class="noticia" data-id="<?= htmlspecialchars($item['dados']['id']) ?>">
                            <a href="noticia.php?id=<?= htmlspecialchars($item['dados']['id']) ?>"
                                class="noticia-link-conteudo">
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
                        </article>
                    <?php elseif ($item['tipo'] === 'anuncio'): ?>
                        <div class="anuncio-intercalado">
                            <?php if ($item['dados']): // Verifica se os dados do anúncio existem ?>
                                <a href="<?= htmlspecialchars($item['dados']['link']) ?>" target="_blank"
                                    title="<?= htmlspecialchars($item['dados']['nome']) ?>">
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

        <?php
        // Exibir anúncios restantes (os que não foram intercalados)
        if (!empty($anunciosRestantes) && count(array_filter($anunciosRestantes)) > 0):
        ?>
            <div class="anuncios-no-final">
                <h3>Outros Anúncios</h3>
                <div class="anuncios-grid-final">
                    <?php foreach($anunciosRestantes as $anuncio): ?>
                        <?php if ($anuncio): // Garante que o anúncio não é nulo ?>
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
        // Script do menu mobile
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('menu').classList.toggle('show');
        });

        // Script para clique e teclado nas notícias (mantido da sua versão)
        document.querySelectorAll('article.noticia').forEach(card => {
            card.addEventListener('click', (e) => {
                if (!e.target.closest('.acoes-noticia a')) {
                    const link = card.querySelector('.noticia-link-conteudo');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });

            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const link = card.querySelector('.noticia-link-conteudo');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });

        // Script do pop-up
        function fecharPopup() {
            document.getElementById("popup-anuncio").style.display = "none";
        }

        window.addEventListener('load', function () {
            setTimeout(function () {
                const popup = document.getElementById("popup-anuncio");
                if (popup) popup.style.display = "flex";
            }, 2000);
        });

        // Script do carrossel
        let slideIndex = 0;
        const carrossel = document.getElementById("carrossel");
        const slides = document.querySelectorAll('.slide');
        const totalSlides = slides.length;

        function mostrarSlide(index) {
            if (index >= totalSlides) slideIndex = 0;
            else if (index < 0) slideIndex = totalSlides - 1;
            else slideIndex = index;
            carrossel.style.transform = 'translateX(' + (-slideIndex * 100) + '%)';
        }

        function mudarSlide(direcao) {
            mostrarSlide(slideIndex + direcao);
        }

        // Inicia o carrossel se houver slides
        if (totalSlides > 0) {
            mostrarSlide(slideIndex); // Garante que o primeiro slide é exibido ao carregar
            setInterval(() => mudarSlide(1), 5000);
        }
    </script>
</body>

</html>