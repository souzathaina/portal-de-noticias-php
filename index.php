<?php
// Inclui a conexão com o banco
require_once 'includes/conexao.php';

// Previsão do tempo - OpenWeather
$cidade = "Sapucaia do Sul,BR"; // Corrigido o erro de digitação aqui
$apiKey = "9c1317cf29a3f077747a2a410f1b5bf8"; // Sua chave da API
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
            <img src="./imagens/anuncio_exemplo_esquerda.png" alt="Anúncio Lateral Esquerdo">
            <p>Seu anúncio aqui na Esquerda!</p>
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
            <img src="./imagens/anuncio_exemplo_direita.png" alt="Anúncio Lateral Direito">
            <p>O seu espaço aqui na Direita!</p>
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

        // Adicionado o script para fazer o card da notícia ser clicável
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