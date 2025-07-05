<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// Verifica se o usuário está logado
if (!usuarioLogado()) {
    header("Location: login.php");
    exit;
}

$usuarioId = $_SESSION['id'];

// Busca os dados atuais do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $usuarioId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado.";
    exit;
}

// Se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $novaFoto = $_FILES['foto'] ?? null;

    // Validação simples
    if (empty($nome) || empty($email)) {
        $erro = "Nome e e-mail são obrigatórios.";
    } else {
        $params = [
            'nome' => $nome,
            'email' => $email,
            'id' => $usuarioId
        ];

        $query = "UPDATE usuarios SET nome = :nome, email = :email";

        // Atualiza senha se foi preenchida
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $query .= ", senha = :senha";
            $params['senha'] = $senhaHash;
        }

        // Upload da nova foto
        if ($novaFoto && $novaFoto['error'] === 0) {
            $pasta = 'imagens/';
            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }

            $nomeUnico = uniqid() . '-' . basename($novaFoto['name']);
            $caminhoCompleto = $pasta . $nomeUnico;

            if (move_uploaded_file($novaFoto['tmp_name'], $caminhoCompleto)) {
                // Remove a imagem antiga, se não for a padrão
                if (!empty($usuario['foto']) && $usuario['foto'] !== 'imagens/perfil_padrao.png') {
                    @unlink($usuario['foto']);
                }

                $query .= ", foto = :foto";
                $params['foto'] = $caminhoCompleto;
                $_SESSION['foto'] = $caminhoCompleto; // atualiza na sessão
            } else {
                $erro = "Erro ao salvar a nova imagem.";
            }
        }

        if (!isset($erro)) {
            $query .= " WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $_SESSION['nome'] = $nome; // atualiza na sessão

            $mensagem = "Dados atualizados com sucesso!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Conta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles/style_editUsuario.css">
</head>

<body>
    <header>
        <img src="imagens/logo/logo.png" alt="Logo Luz & Verdade" class="logo">
        <div class="usuario-area">
            <p class="perfil-usuario"><?= htmlspecialchars($_SESSION['nome']) ?></p>
            <div class="header-actions"> <div class="menu">
                    <a href="telaLogado.php">Voltar</a>
                    <a href="logout.php">Logout</a>
                </div>
                <button id="theme-toggle" class="theme-toggle-button" aria-label="Alternar tema">
                    <span class="icon-light-mode">☀️</span>
                    <span class="icon-dark-mode">🌙</span>
                </button>
                <div class="menu-hamburguer" onclick="toggleMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>


    <main>
        <h1>Editar Conta</h1>

        <?php if (isset($erro)): ?>
            <p class="mensagem-erro"><?= $erro ?></p>
        <?php elseif (isset($mensagem)): ?>
            <p class="mensagem-sucesso"><?= $mensagem ?></p>
        <?php endif; ?>

        <?php if (!empty($usuario['foto'])): ?>
            <p>Foto atual:</p>
            <img src="<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto de perfil" class="foto-perfil-atual">
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Nova foto de perfil (opcional):</label>
            <input type="file" name="foto" accept="image/*">

            <label>Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label>Nova senha (opcional):</label>
            <input type="password" name="senha">

            <button type="submit">Salvar</button>
            <a href="confirmarExclusao.php" class="botao-excluir">Excluir Conta</a>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            const menuHamburguer = document.querySelector('.menu-hamburguer'); // Seleciona o hamburguer
            const menu = document.querySelector('.menu'); // Seleciona o menu real

            // Função para alternar o menu mobile
            window.toggleMenu = function() {
                menu.classList.toggle('open');
            };

            // Carregar o tema salvo no localStorage
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                body.classList.add(savedTheme);
            } else {
                // Se não houver tema salvo, verificar a preferência do sistema
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    body.classList.add('dark-mode');
                } else {
                    body.classList.add('light-mode'); // Adiciona explicitamente 'light-mode' se não for dark
                }
            }

            themeToggle.addEventListener('click', () => {
                if (body.classList.contains('dark-mode')) {
                    body.classList.remove('dark-mode');
                    body.classList.add('light-mode');
                    localStorage.setItem('theme', 'light-mode');
                } else {
                    body.classList.remove('light-mode');
                    body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark-mode');
                }
            });

            // Opcional: Atualizar o tema se a preferência do sistema mudar
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                if (!localStorage.getItem('theme')) { // Só muda se o usuário não tiver setado uma preferência manual
                    if (event.matches) {
                        body.classList.add('dark-mode');
                        body.classList.remove('light-mode');
                    } else {
                        body.classList.add('light-mode');
                        body.classList.remove('dark-mode');
                    }
                }
            });
        });
    </script>
</body>

</html>