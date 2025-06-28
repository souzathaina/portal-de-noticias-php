<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';


if (!usuarioLogado()) {
    header("location: login.php");
    exit;
}

// Pega o ID da notícia via GET ou POST
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

// Se não tiver ID, volta pra tela principal
if ($id <= 0) {
    header("Location: telaLogado.php");
    exit;
}

// Carrega os dados da notícia atual (pra exibir no formulário)
$sql = "SELECT * FROM noticias WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a notícia não existir ou o usuário não for o autor, bloqueia
if (!$noticia || $noticia['autor'] != $_SESSION['id']) {
    echo "Você não tem permissão para alterar esta notícia.";
    exit;
}

// Se enviou o formulário (alteração)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $conteudo = $_POST['noticia'];
    $imagemNome = $noticia['imagem']; // Por padrão mantém a imagem antiga

    // Se o usuário enviou uma nova imagem
    if (!empty($_FILES['imagem']['name'])) {
        $imagem = $_FILES['imagem'];
        $imagemNome = uniqid() . '_' . basename($imagem['name']);
        $caminho = 'imagens/' . $imagemNome;

        if (!move_uploaded_file($imagem['tmp_name'], $caminho)) {
            die("Erro ao salvar a nova imagem.");
        }
    }

    try {
        // Faz o UPDATE da notícia
        $sql = "UPDATE noticias SET titulo = :titulo, noticia = :noticia, imagem = :imagem WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':noticia' => $conteudo,
            ':imagem' => $imagemNome,
            ':id' => $id
        ]);

        header("Location: telaLogado.php");
        exit;

    } catch (PDOException $e) {
        echo "Erro ao alterar notícia: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/style_altNoticia.css">
    <title>Alterar Notícia</title>
</head>

<body>
    <div class="wrapper">
        <header>
            <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
            <a class="voltar-index" href="telaLogado.php">← Voltar</a>
        </header>

        <main>
            <h1>Alterar Notícia</h1>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                <label for="titulo">Título:</label>
                <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($noticia['titulo']) ?>"
                    required>

                <label for="noticia">Conteúdo:</label>
                <textarea name="noticia" id="noticia" rows="8"
                    required><?= htmlspecialchars($noticia['noticia']) ?></textarea>

                <?php if (!empty($noticia['imagem'])): ?>
                    <p>Imagem atual:</p>
                    <img src="imagens/<?= htmlspecialchars($noticia['imagem']) ?>" alt="Imagem da notícia"
                        class="imagem-atual">
                <?php endif; ?>

                <label for="imagem">Nova imagem (opcional):</label>
                <input type="file" name="imagem" id="imagem">

                <button type="submit">Salvar Alterações</button>
            </form>
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

    </div>
</body>

</html>