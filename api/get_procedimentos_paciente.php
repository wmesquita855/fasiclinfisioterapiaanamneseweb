<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

require_once("conexao.php");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['ID_PACIENTE'])) {
    echo json_encode(["sucesso" => false, "erro" => "ID_PACIENTE não fornecido."]);
    exit;
}

$idPaciente = intval($input['ID_PACIENTE']);

$stmt = $conn->prepare("
    SELECT 
        P.IDPRONTU,
        P.DATAPROCED,
        P.DESCRPRONTU,
        P.ID_PROCED,
        PR.NOMEPROCED
    FROM PRONTUARIO P
    INNER JOIN ESPECPROCED EP ON EP.ID_PROCED = P.ID_PROCED AND EP.ID_ESPEC = 6
    LEFT JOIN PROCEDIMENTO PR ON PR.IDPROCED = P.ID_PROCED
    WHERE P.ID_PACIENTE = ?
    ORDER BY P.DATAPROCED DESC
");

$stmt->bind_param("i", $idPaciente);
$stmt->execute();
$result = $stmt->get_result();

$procedimentos = [];

while ($row = $result->fetch_assoc()) {
    $procedimentos[] = $row;
}

$stmt->close();

echo json_encode([
    "sucesso" => true,
    "procedimentos" => $procedimentos
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
