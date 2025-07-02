<?php
// Inclui a conexão com o banco
require_once 'includes/conexao.php';

// Pega as notícias
$sql = "SELECT noticias.id, noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor
        FROM noticias
        JOIN usuarios ON noticias.autor = usuarios.id
        ORDER BY noticias.data DESC";
$stmt = $pdo->query($sql);
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pega os anúncios (exemplo, ajuste tabela/campos conforme seu BD)
$sqlAnuncios = "SELECT id, nome, imagem, link FROM anuncio ORDER BY id ASC";
$stmtAnuncios = $pdo->query($sqlAnuncios);
$anuncios = $stmtAnuncios->fetchAll(PDO::FETCH_ASSOC);

// Previsão do tempo (igual seu código)
$cidade = "Sapucaia do Sul,BR";
$apiKey = "9c1317cf29a3f077747a2a410f1b5bf8";
$url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cidade) . "&appid=$apiKey&units=metric&lang=pt_br";

$tempo = null;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styles/style_index.css" />
  <title>Portal de Notícias</title>
</head>

<body>
  <header>
    <div class="tempo-header">
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
    </div>

    <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo" />

    <div class="menu-toggle" id="menu-toggle">&#9776;</div>

    <nav class="menu" id="menu">
      <a href="index.php">Início</a>
      <a href="login.php">Login / Cadastro</a>
    </nav>
  </header>

  <main>
    <?php if (count($noticias) === 0): ?>
      <p class="mensagem-vazia">Nenhuma notícia publicada ainda.</p>
    <?php else: ?>
      <?php
      $anuncioIndex = 0;
      $totalNoticias = count($noticias);
      $totalAnuncios = count($anuncios);
      
      // Loop em blocos de 3 notícias com anúncio abaixo
      for ($i = 0; $i < $totalNoticias; $i += 3) {
          echo '<div class="blocos-noticia-anuncio">';
          
          // 3 notícias
          for ($j = $i; $j < $i + 3 && $j < $totalNoticias; $j++) {
              $noticia = $noticias[$j];
              ?>
              <a href="noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>" class="noticia-link">
                <article class="noticia">
                  <h2><?= htmlspecialchars($noticia['titulo']) ?></h2>
                  <p class="autor-data"><small>Por <?= htmlspecialchars($noticia['autor']) ?> em <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>
                  <?php if (!empty($noticia['imagem'])): ?>
                    <img src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>" alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>" />
                  <?php endif; ?>
                  <p><?= nl2br(htmlspecialchars(substr($noticia['noticia'], 0, 250))) ?>... <span class="leia-mais">Leia mais</span></p>
                </article>
              </a>
              <?php
          }

          // Exibe anúncio correspondente se existir, senão mostra um anúncio padrão
          if ($anuncioIndex < $totalAnuncios) {
              $anuncio = $anuncios[$anuncioIndex];
              $anuncioIndex++;
              ?>
              <div class="anuncio-box">
                <a href="<?= htmlspecialchars($anuncio['link']) ?>" target="_blank">
                  <img src="imagens/<?= htmlspecialchars($anuncio['imagem']) ?>" alt="<?= htmlspecialchars($anuncio['nome']) ?>" />
                </a>
              </div>
              <?php
          } else {
              // Exemplo anúncio padrão (ajuste imagem e texto conforme necessidade)
              ?>
              <div class="anuncio-box">
                <img src="imagens/anuncio_padrao.png" alt="Anúncio padrão" />
              </div>
              <?php
          }
          echo '</div>';
      }
      ?>
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
          <img src="imagens/icons/facebook.png" alt="Facebook" />
        </a>
        <a href="https://instagram.com/luzeverdade.portal" target="_blank">
          <img src="imagens/icons/instagram.png" alt="Instagram" />
        </a>
        <a href="https://wa.me/5511999999999" target="_blank">
          <img src="imagens/icons/whatsapp.png" alt="WhatsApp" />
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
  </script>
</body>

</html>
