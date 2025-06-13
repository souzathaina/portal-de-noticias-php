<?php
require_once 'funcoes.php';

//redireciona se não estiver logado
if (!usuarioLogado()) {
    header('Location: login.php');
    exit;
}
?>