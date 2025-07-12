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
//$logName = "log_prontuario_" . date("Ymd_His") . "_" . rand(1000, 9999) . ".json";
//file_put_contents($logName, json_encode($dados, JSON_PRETTY_PRINT));

// Verificar campos obrigatórios
if (
    !$dados ||
    !isset($dados['ID_PESSOAFIS']) ||
    !isset($dados['ID_PROFISSIO']) ||
    !isset($dados['ID_PROCED']) ||
    !isset($dados['DATAPROCED']) ||
    !isset($dados['DESCRPRONTU']) ||
    !isset($dados['AUTOPACVISU'])
) {
    echo json_encode(["sucesso" => false, "erro" => "JSON inválido ou campos obrigatórios ausentes."]);
    exit;
}

// Extrair dados
$idPessoafis = intval($dados['ID_PESSOAFIS']);
$idProfissio = intval($dados['ID_PROFISSIO']);
$idProced = intval($dados['ID_PROCED']);
$dataProced = $dados['DATAPROCED'];
$descrProntu = $dados['DESCRPRONTU'];
$autoPacVisu = intval($dados['AUTOPACVISU']);

// Buscar ID_PACIENTE com base no ID_PESSOAFIS
$stmtBusca = $conn->prepare("SELECT IDPACIENTE FROM PACIENTE WHERE ID_PESSOAFIS = ?");
$stmtBusca->bind_param("i", $idPessoafis);
$stmtBusca->execute();
$result = $stmtBusca->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["sucesso" => false, "erro" => "Paciente não encontrado para o ID_PESSOAFIS informado."]);
    exit;
}
$idPaciente = $result->fetch_assoc()['IDPACIENTE'];
$stmtBusca->close();

// Inserir em PRONTUARIO
$stmtPront = $conn->prepare("
    INSERT INTO PRONTUARIO 
    (ID_PACIENTE, ID_PROFISSIO, ID_ESPEC, ID_PROCED, DATAPROCED, DESCRPRONTU, LINKPROCED, AUTOPACVISU)
    VALUES (?, ?, 6, ?, ?, ?, '', ?)
");

if (!$stmtPront) {
    echo json_encode(["sucesso" => false, "erro" => "Erro ao preparar insert PRONTUARIO: " . $conn->error]);
    exit;
}

$stmtPront->bind_param("iiissi", $idPaciente, $idProfissio, $idProced, $dataProced, $descrProntu, $autoPacVisu);

if (!$stmtPront->execute()) {
    echo json_encode(["sucesso" => false, "erro" => "Erro ao executar insert PRONTUARIO: " . $stmtPront->error]);
    exit;
}

$idProntu = $conn->insert_id;
$stmtPront->close();

// ✅ Sucesso
echo json_encode([
    "sucesso" => true,
    "mensagem" => "Prontuário registrado com sucesso.",
    "id_prontuario" => $idProntu
]);
?>
