<?php
// Cabeçalhos CORS e tipo de conteúdo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

// Conexão com o banco principal
require_once "conexao.php";

// Consulta SQL original com alias para nomes antigos
$sql = "
    SELECT 
        IDEXERCICIO AS ID_EXERC, 
        DESCREXERC AS DESC_EXERC, 
        LINKVIDEO AS YOUTUBE_EXERC 
    FROM EXERCICIO 
    ORDER BY DESCREXERC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $exercicios = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($exercicios);
} else {
    echo json_encode([]);
}
?>
