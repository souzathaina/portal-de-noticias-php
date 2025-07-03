<?php
// Inclui a conexão com o banco
require_once 'includes/conexao.php';

// Previsão do tempo - OpenWeather
$cidade = "Sapucaia do Sul,BR";
$apiKey = "9c1317cf29a3f077747a2a410f1b5bf8";
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

// Pega todas as notícias do banco, juntando com o nome do autor (usuario)
$sql = "SELECT noticias.id, noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor
        FROM noticias
        JOIN usuarios ON noticias.autor = usuarios.id
        ORDER BY noticias.data DESC"; // Ordena da mais recente para a mais antiga

$stmt = $pdo->query($sql);
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- CÓDIGO PARA PEGAR E DISTRIBUIR ANÚNCIOS (AJUSTADO) ---
$anunciosEsquerda = [];
$anunciosDireita = [];

try {
    // Buscar todos os anúncios ativos, ordenando por destaque e depois por data de cadastro
    $sqlAnunciosDisponiveis = "SELECT id, nome, imagem, link FROM anuncio WHERE ativo = 1 ORDER BY destaque DESC, data_cadastro DESC";
    $stmtAnunciosDisponiveis = $pdo->query($sqlAnunciosDisponiveis);
    $anunciosDisponiveis = $stmtAnunciosDisponiveis->fetchAll(PDO::FETCH_ASSOC);

    // Embaralha para que a seleção seja aleatória (priorizando destaques por conta do ORDER BY)
    shuffle($anunciosDisponiveis);

    $totalAnuncios = count($anunciosDisponiveis);
    $anunciosAlocados = 0;

    // Aloca até 2 para a esquerda e até 2 para a direita, garantindo distinção
    foreach ($anunciosDisponiveis as $anuncio) {
        if ($anunciosAlocados >= 4) { // Limita a um máximo de 4 anúncios no total (2 por lado)
            break;
        }

        // Tenta adicionar à esquerda primeiro, até o limite de 2
        if (count($anunciosEsquerda) < 2) {
            $anunciosEsquerda[] = $anuncio;
            $anunciosAlocados++;
        }
        // Se a esquerda já tem 2, tenta adicionar à direita, até o limite de 2
        else if (count($anunciosDireita) < 2) {
            // Garante que o anúncio não é repetido entre os lados, se houver apenas 1 ou 2 anúncios no total
            $isRepeated = false;
            foreach($anunciosEsquerda as $aE) {
                if ($aE['imagem'] === $anuncio['imagem']) {
                    $isRepeated = true;
                    break;
                }
            }
            if (!$isRepeated) {
                $anunciosDireita[] = $anuncio;
                $anunciosAlocados++;
            }
        }
    }

    // Se um lado ficou com menos de 2 anúncios e o outro tem mais que o necessário, redistribui
    // Isso é útil se houver 3 anúncios, por exemplo, ele pode tentar fazer 2 na esquerda e 1 na direita
    while (count($anunciosEsquerda) < 2 && count($anunciosDireita) > 0) {
        $anuncioParaMover = array_shift($anunciosDireita);
        $anunciosEsquerda[] = $anuncioParaMover;
    }
    while (count($anunciosDireita) < 2 && count($anunciosEsquerda) > 0) {
        $anuncioParaMover = array_shift($anunciosEsquerda);
        $anunciosDireita[] = $anuncioParaMover;
    }


} catch (PDOException $e) {
    error_log("Erro ao buscar anúncios: " . $e->getMessage());
    $anunciosEsquerda = [];
    $anunciosDireita = [];
}
// --- FIM DO CÓDIGO PARA PEGAR E DISTRIBUIR ANÚNCIOS ---
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="styles/style_index.css">
    <title>Portal de Notícias</title>
</head>

<body>
    <header>
        <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">

        <div class="menu-toggle" id="menu-toggle">&#9776;</div>

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
            </nav>
        </div>
    </header>

    <main>
        <div class="anuncio-lateral anuncio-esquerda">
            <?php if (!empty($anunciosEsquerda)): ?>
                <?php foreach ($anunciosEsquerda as $anuncio): ?>
                    <div class="anuncio-item">
                        <?php if (!empty($anuncio['link'])): ?>
                            <a href="<?= htmlspecialchars($anuncio['link']) ?>" target="_blank">
                        <?php endif; ?>
                        <img src="imagens/<?= htmlspecialchars($anuncio['imagem']) ?>" alt="<?= htmlspecialchars($anuncio['nome']) ?>">
                        <?php if (!empty($anuncio['link'])): ?>
                            </a>
                        <?php endif; ?>
                        <p><?= htmlspecialchars($anuncio['nome']) ?></p>
                    </div>
                <?php endforeach; ?>
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
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <div class="anuncio-lateral anuncio-direita">
            <?php if (!empty($anunciosDireita)): ?>
                <?php foreach ($anunciosDireita as $anuncio): ?>
                    <div class="anuncio-item">
                        <?php if (!empty($anuncio['link'])): ?>
                            <a href="<?= htmlspecialchars($anuncio['link']) ?>" target="_blank">
                        <?php endif; ?>
                        <img src="imagens/<?= htmlspecialchars($anuncio['imagem']) ?>" alt="<?= htmlspecialchars($anuncio['nome']) ?>">
                        <?php if (!empty($anuncio['link'])): ?>
                            </a>
                        <?php endif; ?>
                        <p><?= htmlspecialchars($anuncio['nome']) ?></p>
                    </div>
                <?php endforeach; ?>
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
    <script>
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('menu').classList.toggle('show');
        });

        document.querySelectorAll('article.noticia').forEach(card => {
            card.addEventListener('click', (e) => {
                // Previne o clique nos botões Alterar/Excluir de ativar o link do card
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
    </script>
</body>
</html>