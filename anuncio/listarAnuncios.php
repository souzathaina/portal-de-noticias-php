<?php
session_start(); // Inicia a sessão
require_once '../includes/conexao.php'; // Inclui a conexão com o banco de dados
require_once'../includes/funcoes.php';
// --- VERIFICAÇÃO DE SEGURANÇA E PERMISSÃO ---
// Redireciona para a página inicial se o usuário não estiver logado ou não for administrador
if (!usuarioLogado()) {
  header("Location: ../index.php");
  exit();
}

// --- LÓGICA DA PREVISÃO DO TEMPO (para o cabeçalho) ---
// Note: Você pode querer centralizar a lógica da API de tempo se ela for usada em muitas páginas.
$cidade = "Sapucaia do Sul,BR";
$apiKey = "9c1317cf29a3f077747a2a410f1b5bf8"; // Sua chave da API do OpenWeatherMap
$url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cidade) . "&appid=$apiKey&units=metric&lang=pt_br";

$tempo = null; // Inicializa a variável $tempo
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout de 5 segundos
// Em ambiente de desenvolvimento, curl_setopt(..., CURLOPT_SSL_VERIFYPEER, false); pode ser necessário.
// Em produção, remova ou defina como true e garanta um certificado SSL válido.
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
// --- FIM DA LÓGICA DA PREVISÃO DO TEMPO ---

// Busca todos os anúncios do banco de dados
// Confirmado: sua tabela se chama 'anuncio'.
$sql = "SELECT id, nome, imagem, link, destaque, ativo FROM anuncio ORDER BY destaque DESC, data_cadastro DESC";
$anuncios = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gerenciar Anunciantes</title>
  <link rel="stylesheet" href="../styles/style_index.css" />
  <link rel="stylesheet" href="../styles/style_listaAnuncios.css" />
</head>

<body>

  <header>
    <div class="tempo-area">
      <?php if (isset($tempo) && $tempo): ?>
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

    <img src="../imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">

    <div class="menu-toggle" id="menu-toggle">&#9776;</div>

    <nav class="menu" id="menu">
      <a href="cadastrarAnuncio.php">Cadastrar Anúncio</a>
      <a href="../telaLogado.php">Início</a>
      <a href="../logout.php">Logout</a>
      <button class="theme-toggle-button" id="theme-toggle">
          <span class="icon-light-mode">☀️</span>
          <span class="icon-dark-mode">🌙</span>
      </button>
    </nav>
  </header>

  <main>
    <h1>Lista de Anunciantes</h1>
    <a href="cadastrarAnuncio.php" class="novo-anuncio">+ Novo Anunciante</a>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Imagem</th>
          <th>Link</th>
          <th>Destaque</th>
          <th>Ativo</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($anuncios)): ?>
          <tr>
            <td colspan="7">Nenhum anunciante cadastrado ainda.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($anuncios as $anuncio): ?>
            <tr>
              <td data-label="ID"><?= htmlspecialchars($anuncio['id']) ?></td>
              <td data-label="Nome"><?= htmlspecialchars($anuncio['nome']) ?></td>
              <td data-label="Imagem">
                <?php if (!empty($anuncio['imagem'])): ?>
                  <img src="../imagens/<?= htmlspecialchars($anuncio['imagem']) ?>" alt="Anúncio">
                <?php else: ?>
                  (sem imagem)
                <?php endif; ?>
              </td>
              <td data-label="Link">
                <?php if (!empty($anuncio['link'])): ?>
                  <a href="<?= htmlspecialchars($anuncio['link']) ?>" target="_blank" rel="noopener noreferrer">Visitar</a>
                <?php else: ?>
                  (sem link)
                <?php endif; ?>
              </td>
              <td data-label="Destaque" data-boolean="<?= $anuncio['destaque'] ?>">
                <?= $anuncio['destaque'] ? 'Sim' : 'Não' ?>
              </td>
              <td data-label="Ativo" data-boolean="<?= $anuncio['ativo'] ?>">
                <?= $anuncio['ativo'] ? 'Sim' : 'Não' ?>
              </td>
              <td class="acoes" data-label="Ações">
                <a href="editarAnuncio.php?id=<?= htmlspecialchars($anuncio['id']) ?>">Editar</a> |
                <a href="excluirAnuncio.php?id=<?= htmlspecialchars($anuncio['id']) ?>"
                  onclick="return confirm('Tem certeza que deseja excluir este anunciante?');">Excluir</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
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
        <a href="https://facebook.com/luzeverdadeoficial" target="_blank" rel="noopener noreferrer">
          <img src="../imagens/icons/facebook.png" alt="Facebook">
        </a>
        <a href="https://instagram.com/luzeverdade.portal" target="_blank" rel="noopener noreferrer">
          <img src="../imagens/icons/instagram.png" alt="Instagram">
        </a>
        <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer">
          <img src="../imagens/icons/whatsapp.png" alt="WhatsApp">
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