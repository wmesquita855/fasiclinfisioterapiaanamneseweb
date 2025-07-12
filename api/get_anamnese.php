<?php
header('Content-Type: application/json');
require_once "conexao.php";

$idPaciente = isset($_GET['idPaciente']) ? intval($_GET['idPaciente']) : 0;
$idProf     = isset($_GET['idProf']) ? intval($_GET['idProf']) : 0;
$idAnamnese = isset($_GET['idAnamnese']) ? intval($_GET['idAnamnese']) : 0;

$sql = "SELECT * FROM ANAMNESE WHERE 1=1";
if ($idAnamnese > 0) {
    $sql .= " AND IDANAMNESE = $idAnamnese";
}
if ($idPaciente > 0) {
    $sql .= " AND ID_PACIENTE = $idPaciente";
}
if ($idProf > 0) {
    $sql .= " AND ID_PROFISSIO = $idProf";
}
$sql .= " ORDER BY DATAANAM DESC";

$res = $conn->query($sql);
if ($res) {
    $dados = $res->fetch_all(MYSQLI_ASSOC);
    if (count($dados) === 0) {
        echo json_encode(["mensagem" => "Nenhuma anamnese encontrada."]);
    } else {
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(["erro" => "Erro na consulta SQL: " . $conn->error]);
}
?>
