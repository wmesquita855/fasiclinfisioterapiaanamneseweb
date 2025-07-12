<?php
// conexao2.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn2 = new mysqli("108.181.92.77", "appfisio", "RHMjSSbh5svG*vsR", "fisioclin", 3306);

if ($conn2->connect_error) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro na conexão com o banco: " . $conn2->connect_error]);
    exit;
}
?>
