<?php


if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function usuarioLogado()
{
    return isset(($_SESSION['usuario_id']));
}

function idUsuarioLogado()
{
    return $_SESSION['usuario_id'] ?? null;
}
?>