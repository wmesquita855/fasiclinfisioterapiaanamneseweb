<?php
// Cabeçalhos CORS para permitir requisições de qualquer origem
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

require_once "conexao.php";

$sql = "SELECT 
            p.IDPROCED,
            p.CODPROCED,
            p.DESCRPROC,
            p.VALORPROC
        FROM PROCEDIMENTO p
        INNER JOIN ESPECPROCED ep ON ep.ID_PROCED = p.IDPROCED
        WHERE ep.ID_ESPEC = 6
        ORDER BY p.DESCRPROC";

$res = $conn->query($sql);

if ($res) {
    $dados = $res->fetch_all(MYSQLI_ASSOC);
    echo json_encode($dados);
} else {
    echo json_encode(["erro" => "Erro na consulta SQL: " . $conn->error]);
}
?>
