<?php
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT nome, imagem, link, destaque FROM anuncio WHERE ativo = 1 ORDER BY destaque DESC, data_cadastro DESC";
    $stmt = $pdo->query($sql);
    $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($anuncios, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['erro' => 'Erro ao buscar os anúncios: ' . $e->getMessage()]);
}
