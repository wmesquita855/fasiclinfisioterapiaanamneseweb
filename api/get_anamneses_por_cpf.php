<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("conexao.php");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['CPF'])) {
    echo json_encode(["sucesso" => false, "erro" => "CPF do paciente não fornecido."]);
    exit;
}

$cpf = preg_replace('/[^0-9]/', '', $input['CPF']); // sanitiza

$response = ["sucesso" => true];

// Buscar dados do paciente
$stmtPessoa = $conn->prepare("
  SELECT ID_PESSOAFIS, CPFPESSOA, NOMEPESSOA, DATANASCPES, SEXOPESSOA 
  FROM PESSOAFIS 
  WHERE CPFPESSOA = ?
");
$stmtPessoa->bind_param("s", $cpf);
$stmtPessoa->execute();
$resPessoa = $stmtPessoa->get_result();

if ($resPessoa->num_rows === 0) {
    echo json_encode(["sucesso" => false, "erro" => "CPF não encontrado."]);
    exit;
}

$dadosPaciente = $resPessoa->fetch_assoc();
$idPessoafis = $dadosPaciente['ID_PESSOAFIS'];
$response["paciente"] = [
    "cpf" => $dadosPaciente["CPFPESSOA"],
    "nome" => $dadosPaciente["NOMEPESSOA"],
    "dataNascimento" => $dadosPaciente["DATANASCPES"],
    "sexo" => $dadosPaciente["SEXOPESSOA"]
];
$stmtPessoa->close();

// Buscar IDPACIENTE
$stmtPac = $conn->prepare("SELECT IDPACIENTE FROM PACIENTE WHERE ID_PESSOAFIS = ?");
$stmtPac->bind_param("i", $idPessoafis);
$stmtPac->execute();
$resPac = $stmtPac->get_result();

if ($resPac->num_rows === 0) {
    echo json_encode(["sucesso" => false, "erro" => "Paciente não encontrado para o CPF."]);
    exit;
}
$idPaciente = $resPac->fetch_assoc()['IDPACIENTE'];
$stmtPac->close();

// Buscar anamneses da especialidade 6
$stmtAnam = $conn->prepare("
  SELECT A.IDANAMNESE, A.DATAANAM, A.NOMERESP, A.CPFRESP, A.OBSERVACOES
  FROM ANAMNESE A
  INNER JOIN PRONTUARIO P ON P.ID_PACIENTE = A.ID_PACIENTE AND P.ID_PROFISSIO = A.ID_PROFISSIO
  WHERE A.ID_PACIENTE = ?
    AND P.ID_PROCED IN (
        SELECT ID_PROCED FROM ESPECPROCED WHERE ID_ESPEC = 6
    )
  GROUP BY A.IDANAMNESE
  ORDER BY A.DATAANAM DESC
");
$stmtAnam->bind_param("i", $idPaciente);
$stmtAnam->execute();
$resAnam = $stmtAnam->get_result();

$response["anamneses"] = [];
while ($row = $resAnam->fetch_assoc()) {
    $response["anamneses"][] = $row;
}
$stmtAnam->close();

// Buscar prontuários da especialidade 6
$stmtPront = $conn->prepare("
  SELECT IDPRONTU, DATAPROCED, DESCRPRONTU, LINKPROCED, AUTOPACVISU
  FROM PRONTUARIO
  WHERE ID_PACIENTE = ?
    AND ID_PROCED IN (
        SELECT ID_PROCED FROM ESPECPROCED WHERE ID_ESPEC = 6
    )
  ORDER BY DATAPROCED DESC
");
$stmtPront->bind_param("i", $idPaciente);
$stmtPront->execute();
$resPront = $stmtPront->get_result();

$response["prontuarios"] = [];
while ($row = $resPront->fetch_assoc()) {
    $response["prontuarios"][] = $row;
}
$stmtPront->close();

// Resposta final
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
