<?php


if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function usuarioLogado()
{

    return isset(($_SESSION['nome']));
}
function pegarIdAutor()
{

    if (isset($_SESSION['id'])) {
        $autor = $_SESSION['id'];
    }
    ;

}
function idUsuarioLogado()
{
    return $_SESSION['id'] ?? null;
}
?>