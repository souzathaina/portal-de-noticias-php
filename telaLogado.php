<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// Verifica se o usuário está logado
if (!usuarioLogado()) {
    header("location: cadastro.php");
    exit;
}

// Pega todas as notícias do banco, junto com o nome do autor (usuário)
$sql = "SELECT noticias.id, noticias.titulo, noticias.noticia, noticias.data, noticias.imagem, usuarios.nome AS autor, usuarios.id AS id_autor
        FROM noticias
        JOIN usuarios ON noticias.autor = usuarios.id
        ORDER BY noticias.data DESC";
$stmt = $pdo->query($sql);
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="styles/style_telaLogado.css">
    <title>Portal de Notícias</title>
</head>

<body>
    <header>
        <!-- Logo -->
        <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">

        <!-- Ícone do menu sanduíche (hamburger) -->
        <div class="menu-toggle" id="menu-toggle">&#9776;</div>

        <!-- Área do usuário e menu -->
        <div class="usuario-area">
            <div class="perfil-usuario">
                <?php
                $fotoUsuario = !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'imagens/perfil_padrao.png';
                ?>
                <img src="<?= htmlspecialchars($fotoUsuario) ?>" alt="Foto do perfil">
                <p><?= htmlspecialchars($_SESSION['nome']) ?></p>
            </div>

            <nav class="menu" id="menu">
                <a href="cadastrarNoticia.php">Criar notícia</a>
                <a href="editarUsuario.php">Editar Usuário</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="noticia-anuncio">
            <div class="anuncio"><img src="./imagens/perfil_padrao.png" alt="">teste</div>
            <div class="separador-colorido"></div>

            <?php if (count($noticias) == 0): ?>
                <p class="mensagem-vazia">Nenhuma notícia publicada ainda.</p>
            <?php else: ?>
                <div class="noticias-grid">
                    <?php foreach ($noticias as $index => $noticia): ?>
                        <?php
                        $area = '';
                        if ($index === 0)
                            $area = 'item1';
                        if ($index === 1)
                            $area = 'item2';
                        if ($index === 2)
                            $area = 'item3';
                        ?>

                        <a href="noticia.php?id=<?= htmlspecialchars($noticia['id']) ?>" class="noticia-link">
                            <article class="noticia <?= $area ?>">
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
                            </article>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="separador-colorido"></div>
            <div class="anuncio"><img src="./imagens/perfil_padrao.png" alt="">teste</div>
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



    <!-- Script para abrir/fechar o menu sanduíche -->
    <script>
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