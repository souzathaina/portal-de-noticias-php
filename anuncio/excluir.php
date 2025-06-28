<?php
require_once '../includes/conexao.php';

$id = $_GET['id'];
$conn->query("DELETE FROM anuncio WHERE id = $id");

header("Location: listar.php");
