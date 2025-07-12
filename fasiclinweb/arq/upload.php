<?php
$destino = "../arq/";

if (!isset($_FILES['arquivo'])) {
    echo "Nenhum arquivo enviado.";
    exit;
}

$arquivo = $_FILES['arquivo'];

if ($arquivo['type'] !== 'image/jpeg') {
    echo "Formato inválido. Apenas JPEG.";
    exit;
}

if ($arquivo['size'] > 20 * 1024 * 1024) {
    echo "Arquivo excede o limite de 20MB.";
    exit;
}

$nomeFinal = uniqid() . ".jpg";
move_uploaded_file($arquivo['tmp_name'], "$destino$nomeFinal");

echo "Upload concluído: $nomeFinal";
?>
