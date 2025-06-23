<?php
session_start();
session_unset(); // Remove todas as variáveis de sessão
session_destroy(); // Encerra a sessão completamente


setcookie("usuarioLogin", $usuario, time() - 3600);

// Redireciona o usuário para a página de login
header("Location: login.php");
exit;
?>