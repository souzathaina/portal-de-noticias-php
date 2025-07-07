<?php
require_once '../includes/conexao.php';
// require_once '../includes/funcoes.php'; // Removido, pois a lógica do tempo será incluída aqui diretamente

// Início da lógica para obter o tempo (AGORA DIRETO NESTE ARQUIVO)
$cidade = "Sapucaia do Sul"; // Mantém a cidade padrão que você usa
$pais = "BR"; // Adicionei o código do país para a requisição
$apiKey = "9c1317cf29a3f077747a2a410f1b5bf8"; // Sua chave da API, conforme a index.php

$url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cidade) . "," . urlencode($pais) . "&appid=$apiKey&units=metric&lang=pt_br";

$tempo = null; // Inicializa como null

// Faz a requisição com cURL, igual à sua index.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // segundos
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ignora certificado SSL (se você tiver problemas com certificados em ambiente local)

$response = curl_exec($ch);
$erroCurl = curl_error($ch);
curl_close($ch);

if ($response && !$erroCurl) {
    $dados = json_decode($response, true);
    if (isset($dados['main'])) {
        $tempo = [
            'temperatura' => round($dados['main']['temp']),
            'descricao' => ucfirst($dados['weather'][0]['description']),
            'icone' => $dados['weather'][0]['icon']
        ];
    }
}
// Fim da lógica para obter o tempo

$id = $_GET['id'];
$sql = "SELECT * FROM anuncio WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$anuncio = $stmt->fetch();

if (!$anuncio) {
    echo "Anunciante não encontrado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'];
    $link = $_POST['link'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $valor = str_replace(',', '.', $_POST['valorAnuncio']); // Garante que é ponto decimal para o banco

    $imagemNome = $anuncio['imagem']; // Mantém a imagem antiga

    if (!empty($_FILES['imagem']['name'])) {
        $imagem = $_FILES['imagem'];
        $imagemNome = uniqid() . '_' . basename($imagem['name']);
        $caminho = '../imagens/' . $imagemNome;

        // Verifica se o diretório de destino existe e é gravável
        if (!is_dir('../imagens')) {
            mkdir('../imagens', 0777, true);
        }

        if (!move_uploaded_file($imagem['tmp_name'], $caminho)) {
            die("Erro ao salvar a imagem.");
        }

        // Apaga a imagem antiga apenas se uma nova foi enviada e a antiga existia
        if (!empty($anuncio['imagem']) && file_exists('../imagens/' . $anuncio['imagem'])) {
            unlink('../imagens/' . $anuncio['imagem']);
        }
    }

    $sql = "UPDATE anuncio SET nome=?, imagem=?, link=?, ativo=?, destaque=?, valorAnuncio=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $imagemNome, $link, $ativo, $destaque, $valor, $id]);

    header("Location: listarAnuncios.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../styles/style_index.css"> <link rel="stylesheet" href="../styles/style_cadAnuncio.css">
    <title>Editar Anunciante</title>
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
            <a href="listarAnuncios.php">Voltar</a>
            <a href="../telaLogado.php">Início</a>
            <button class="theme-toggle-button" id="theme-toggle">
                <span class="icon-light-mode">☀️</span>
                <span class="icon-dark-mode">🌙</span>
            </button>
        </nav>
    </header>

    <main>
        <section class="form-container">
            <h2>Editar Anunciante</h2>
            <form method="post" enctype="multipart/form-data" class="form-anunciante">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($anuncio['nome']) ?>">

                <label>Imagem atual:</label><br>
                <?php if ($anuncio['imagem']): ?>
                    <img src="../imagens/<?= htmlspecialchars($anuncio['imagem']) ?>" alt="Imagem atual"
                        style="max-width: 150px; display: block; margin-bottom: 10px;"><br>
                <?php else: ?>
                    <small>(sem imagem)</small><br>
                <?php endif; ?>

                <label for="imagem">Alterar imagem (arquivo):</label>
                <input type="file" id="imagem" name="imagem">

                <label for="link">Link:</label>
                <input type="text" id="link" name="link" value="<?= htmlspecialchars($anuncio['link']) ?>">

                <label for="valorAnuncio">Valor (R$):</label>
                <input type="number" step="0.01" id="valorAnuncio" name="valorAnuncio"
                    value="<?= htmlspecialchars($anuncio['valorAnuncio']) ?>">

                <label class="checkbox-label">
                    <input type="checkbox" name="ativo" <?= $anuncio['ativo'] ? 'checked' : '' ?>> Ativo
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="destaque" <?= $anuncio['destaque'] ? 'checked' : '' ?>> Destaque
                </label>

                <div class="botoes">
                    <button type="submit">Atualizar</button>
                    <a href="listarAnuncios.php" class="btn-voltar">Voltar</a>
                </div>
            </form>
        </section>
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