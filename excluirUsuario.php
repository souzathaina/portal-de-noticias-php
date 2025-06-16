<?php
session_start();
require_once 'includes/conexao.php';
require_once 'includes/funcoes.php';

// ✅ Verifica se o usuário está logado
if (!usuarioLogado()) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id'];

// Exclui todas as notícias do usuário para evitar restrição de chave estrangeira
$sqlExcluirNoticias = "DELETE FROM noticias WHERE autor = ?";
$stmtNoticias = $pdo->prepare($sqlExcluirNoticias);
$stmtNoticias->execute([$id_usuario]);

// Exclui o usuário logado
$sqlExcluirUsuario = "DELETE FROM usuarios WHERE id = ?";
$stmtUsuario = $pdo->prepare($sqlExcluirUsuario);
$stmtUsuario->execute([$id_usuario]);

// Encerra a sessão para efetuar logout automático após exclusão
session_unset();
session_destroy();

// Redireciona para a página inicial pública
header("Location: index.php");
exit;
