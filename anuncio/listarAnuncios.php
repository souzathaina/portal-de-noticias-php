<?php 
require_once '../includes/conexao.php';

$sql = "SELECT * FROM anuncio ORDER BY destaque DESC, data_cadastro DESC";
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

  <!-- Início Header -->
  <header>
    <div class="tempo-header">
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
    </nav>
  </header>

  <script>
    document.getElementById('menu-toggle').addEventListener('click', function () {
      document.getElementById('menu').classList.toggle('show');
    });
  </script>

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
        <?php foreach ($anuncios as $anuncio): ?>
          <tr>
            <td><?= $anuncio['id'] ?></td>
            <td><?= htmlspecialchars($anuncio['nome']) ?></td>
            <td>
              <?php if (!empty($anuncio['imagem'])): ?>
                <img src="../imagens/<?= htmlspecialchars($anuncio['imagem']) ?>" alt="Anúncio"
                  style="max-width:100px; max-height:60px;">
              <?php else: ?>
                (sem imagem)
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($anuncio['link'])): ?>
                <a href="<?= htmlspecialchars($anuncio['link']) ?>" target="_blank" rel="noopener noreferrer">Visitar</a>
              <?php else: ?>
                (sem link)
              <?php endif; ?>
            </td>
            <td><?= $anuncio['destaque'] ? 'Sim' : 'Não' ?></td>
            <td><?= $anuncio['ativo'] ? 'Sim' : 'Não' ?></td>
            <td class="acoes">
              <a href="editarAnuncio.php?id=<?= $anuncio['id'] ?>">Editar</a> |
              <a href="excluirAnuncio.php?id=<?= $anuncio['id'] ?>"
                onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>

  <!-- Início Footer -->
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

</body>

</html>
