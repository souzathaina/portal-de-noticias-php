<?php
require_once '../includes/conexao.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $sql = "DELETE FROM anuncio WHERE id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$id]);
}

header("Location: listarAnuncios.php");
exit;