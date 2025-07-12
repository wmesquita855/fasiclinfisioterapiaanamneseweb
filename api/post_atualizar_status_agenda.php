<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("conexao.php");

$dados = json_decode(file_get_contents("php://input"), true);

// Log opcional
$logName = "log_status_agenda_" . date("Ymd_His") . "_" . rand(1000, 9999) . ".json";
file_put_contents($logName, json_encode($dados, JSON_PRETTY_PRINT));

// Verifica campos obrigatórios
if (
    !$dados ||
    !isset($dados['ID_AGENDA']) ||
    !isset($dados['STATUSAGEN'])
) {
    echo json_encode(["sucesso" => false, "erro" => "JSON inválido ou campos obrigatórios ausentes."]);
    exit;
}

$idAgenda = intval($dados['ID_AGENDA']);
$status = intval($dados['STATUSAGEN']);

// Atualizar o campo correto: SITUAGEN
$stmt = $conn->prepare("UPDATE AGENDA SET SITUAGEN = ? WHERE IDAGENDA = ?");
if (!$stmt) {
    echo json_encode(["sucesso" => false, "erro" => "Erro ao preparar atualização: " . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $status, $idAgenda);
if (!$stmt->execute()) {
    echo json_encode(["sucesso" => false, "erro" => "Erro ao executar atualização: " . $stmt->error]);
    exit;
}

$stmt->close();

// Sucesso
echo json_encode([
    "sucesso" => true,
    "mensagem" => "SITUAGEN da agenda atualizado com sucesso.",
    "id_agenda" => $idAgenda,
    "situagen_aplicado" => $status
]);
?>
