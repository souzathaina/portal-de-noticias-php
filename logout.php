<?php
session_start();
session_unset(); // Remove todas as variáveis de sessão
session_destroy(); // Encerra a sessão completamente

// Redireciona o usuário para a página de login
header("Location: login.php");
exit;
?>