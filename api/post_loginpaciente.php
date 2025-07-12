<?php
// Cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

require_once "conexao.php";

// Entrada JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['cpf']) || !isset($input['token'])) {
    echo json_encode(["sucesso" => false, "erro" => "CPF e token são obrigatórios."]);
    exit;
}

$cpf = $input['cpf'];
$token = $input['token'];

// 1. Verifica se o CPF pertence a uma pessoa e se é paciente
$sql = "SELECT PF.IDPESSOAFIS, PF.NOMEPESSOA, PF.CPFPESSOA, PAC.IDPACIENTE
        FROM PESSOAFIS PF
        INNER JOIN PACIENTE PAC ON PAC.ID_PESSOAFIS = PF.IDPESSOAFIS
        WHERE PF.CPFPESSOA = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["sucesso" => false, "erro" => "Paciente não encontrado."]);
    exit;
}

$paciente = $result->fetch_assoc();
$idPaciente = $paciente['IDPACIENTE'];
$nomePessoa = $paciente['NOMEPESSOA'];
$cpfPessoa = $paciente['CPFPESSOA'];

// 2. Verifica se o token está associado a uma anamnese do paciente
$sql = "SELECT A.IDANAMNESE, T.IDTOKENFISIO, T.TOKEN
        FROM ANAMNESE A
        INNER JOIN TOKENFISIO T ON T.ID_ANAMNESE = A.IDANAMNESE
        WHERE A.ID_PACIENTE = ? AND T.TOKEN = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $idPaciente, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["sucesso" => false, "erro" => "Token inválido ou sem anamnese vinculada."]);
    exit;
}

$anamneseData = $result->fetch_assoc();
$idAnamnese = $anamneseData['IDANAMNESE'];
$idToken = $anamneseData['IDTOKENFISIO'];

// 3. Buscar EXERCPRESC vinculados à anamnese
$sql = "SELECT IDEXERCPRESC, ID_EXERCICIO, ID_ANAMNESE, QTDEXERC, ORIENTACAO
        FROM EXERCPRESC
        WHERE ID_ANAMNESE = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idAnamnese);
$stmt->execute();
$result = $stmt->get_result();

$exercPresc = [];
$exercRealizados = [];

while ($row = $result->fetch_assoc()) {
    $exercPresc[] = $row;

    // 4. Buscar EXERCREALIZADO para cada EXERCPRESC
    $idExercPresc = $row['IDEXERCPRESC'];
    $sqlRealizado = "SELECT IDEXERCREALIZADO, ID_EXERCPRESC, DATAHORA
                     FROM EXERCREALIZADO
                     WHERE ID_EXERCPRESC = ?";
    $stmtRealizado = $conn->prepare($sqlRealizado);
    $stmtRealizado->bind_param("i", $idExercPresc);
    $stmtRealizado->execute();
    $resRealizado = $stmtRealizado->get_result();

    while ($r = $resRealizado->fetch_assoc()) {
        $exercRealizados[] = $r;
    }
}

// 5. Buscar todos os exercícios cadastrados
$sql = "SELECT IDEXERCICIO, DESCREXERC, LINKVIDEO FROM EXERCICIO";
$result = $conn->query($sql);

$exercicios = [];
while ($row = $result->fetch_assoc()) {
    $exercicios[] = $row;
}

// 6. Resposta final estruturada
echo json_encode([
    "sucesso" => true,
    "TOKENFISIO" => [
        "IDTOKENFISIO" => $idToken,
        "IDANAMNESE" => $idAnamnese,
        "TOKEN" => $token,
        "CPF" => $cpfPessoa,
        "NOME" => $nomePessoa
    ],
    "EXERCPRESC" => $exercPresc,
    "EXERCREALIZADO" => $exercRealizados,
    "EXERCICIO" => $exercicios
]);
?>
