<?php
// conexao.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("160.20.22.99", "aluno22", "Iz+RRU2mIYE=", "fasiclin", 3360);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro na conexão com o banco: " . $conn->connect_error]);
    exit;
}
?>
