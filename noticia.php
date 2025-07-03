<?php
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// Verifica se o parâmetro 'id' foi passado via GET e é numérico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Notícia inválida.");
}

$id = (int) $_GET['id'];

// Busca a notícia no banco, juntando o nome do autor
$sql = "SELECT noticias.titulo, noticias.noticia, noticias.imagem, usuarios.nome AS autor, noticias.data
        FROM noticias
        JOIN usuarios ON noticias.autor = usuarios.id
        WHERE noticias.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$noticia) {
    die("Notícia não encontrada.");
}

// --- Lógica para buscar anúncios para as asides ---
$anuncioEsquerda = null;
$anuncioDireita = null;

try {
    // Buscar todos os anúncios ativos, ordenando por destaque e depois por data de cadastro
    $sqlAnunciosDisponiveis = "SELECT id, nome, imagem, link FROM anuncio WHERE ativo = 1 ORDER BY destaque DESC, data_cadastro DESC";
    $stmtAnunciosDisponiveis = $pdo->query($sqlAnunciosDisponiveis);
    $anunciosDisponiveis = $stmtAnunciosDisponiveis->fetchAll(PDO::FETCH_ASSOC);

    // Embaralha para que a seleção seja mais "aleatória" entre os destaques
    shuffle($anunciosDisponiveis);

    $anunciosAlocados = 0;
    $tempAnuncios = []; // Para armazenar os 2 primeiros anúncios únicos encontrados

    foreach ($anunciosDisponiveis as $anuncio) {
        if ($anunciosAlocados >= 2) { // Limita a um máximo de 2 anúncios no total
            break;
        }

        // Adiciona o anúncio se a imagem ainda não foi usada
        $imagemJaUsada = false;
        foreach ($tempAnuncios as $tempAnuncio) {
            if ($tempAnuncio['imagem'] === $anuncio['imagem']) {
                $imagemJaUsada = true;
                break;
            }
        }

        if (!$imagemJaUsada) {
            $tempAnuncios[] = $anuncio;
            $anunciosAlocados++;
        }
    }

    // Atribui aos anúncios de esquerda e direita
    if (isset($tempAnuncios[0])) {
        $anuncioEsquerda = $tempAnuncios[0];
    }
    if (isset($tempAnuncios[1])) {
        $anuncioDireita = $tempAnuncios[1];
    }

} catch (PDOException $e) {
    error_log("Erro ao buscar anúncios para a página de notícia: " . $e->getMessage());
    $anuncioEsquerda = null;
    $anuncioDireita = null;
}
// --- FIM DA LÓGICA DE ANÚNCIOS ---
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($noticia['titulo']) ?></title>
    <link rel="stylesheet" href="styles/style_noticia.css" />
</head>

<body>
    <header>
        <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
        <div class="header-texto">
            <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>
            <p><small>Por <?= htmlspecialchars($noticia['autor']) ?> em
                        <?= date('d/m/Y H:i', strtotime($noticia['data'])) ?></small></p>
        </div>
        <?php
        if (usuarioLogado()) { // Assumo que essa função existe no funcoes.php
            echo '<a href="telaLogado.php" class="botao-voltar">⬅ Voltar para o início</a>';
        } else {
            echo '<a href="index.php" class="botao-voltar">⬅ Voltar para o início</a>';
        }
        ?>
    </header>

    <div class="container">
        <aside class="esquerda">
            <?php if ($anuncioEsquerda): ?>
                <a href="<?= htmlspecialchars($anuncioEsquerda['link']) ?>" target="_blank" title="<?= htmlspecialchars($anuncioEsquerda['nome']) ?>">
                    <img src="imagens/<?= htmlspecialchars($anuncioEsquerda['imagem']) ?>" alt="Anúncio: <?= htmlspecialchars($anuncioEsquerda['nome']) ?>">
                    <p><?= htmlspecialchars($anuncioEsquerda['nome']) ?></p>
                </a>
            <?php else: ?>
                <img src="./imagens/anuncio_exemplo_esquerda.png" alt="Anúncio Lateral Esquerdo">
                <p>Anuncie aqui!</p>
            <?php endif; ?>
        </aside>

        <main>
            <section class="noticia-conteudo">
                <?php if (!empty($noticia['imagem'])): ?>
                    <img src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>?v=<?= time() ?>"
                         alt="Imagem da notícia: <?= htmlspecialchars($noticia['titulo']) ?>" loading="lazy" />
                <?php endif; ?>

                <p>
                    <?php
                    $textoNoticia = htmlspecialchars($noticia['noticia']);
                    // Adiciona quebras de linha a cada 800 caracteres se não houver quebra de linha natural
                    // Isso é uma heurística para textos muito longos sem formatação
                    if (strpos($textoNoticia, "\n") === false && strlen($textoNoticia) > 800) { 
                        $textoFormatado = wordwrap($textoNoticia, 800, "<br /><br />\n", true);
                        echo $textoFormatado;
                    } else {
                        echo nl2br($textoNoticia); // Usa nl2br para quebras de linha existentes
                    }
                    ?>
                </p>
            </section>
        </main>

        <aside class="direita">
            <?php if ($anuncioDireita): ?>
                <a href="<?= htmlspecialchars($anuncioDireita['link']) ?>" target="_blank" title="<?= htmlspecialchars($anuncioDireita['nome']) ?>">
                    <img src="imagens/<?= htmlspecialchars($anuncioDireita['imagem']) ?>" alt="Anúncio: <?= htmlspecialchars($anuncioDireita['nome']) ?>">
                    <p><?= htmlspecialchars($anuncioDireita['nome']) ?></p>
                </a>
            <?php else: ?>
                <img src="./imagens/anuncio_exemplo_direita.png" alt="Anúncio Lateral Direito">
                <p>Anuncie aqui!</p>
            <?php endif; ?>
        </aside>
    </div>

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
</body>

</html>