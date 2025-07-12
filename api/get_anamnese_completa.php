<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

require_once("conexao.php");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['ID_ANAMNESE'])) {
    echo json_encode(["sucesso" => false, "erro" => "ID_ANAMNESE não fornecido."]);
    exit;
}

$idAnamnese = intval($input['ID_ANAMNESE']);
$response = ["sucesso" => true];

// Buscar dados da ANAMNESE
$stmt = $conn->prepare("
    SELECT IDANAMNESE, ID_PACIENTE, ID_PROFISSIO, DATAANAM, NOMERESP, CPFRESP, 
           AUTVISIB, STATUSANM, STATUSFUNC, OBSERVACOES
    FROM ANAMNESE
    WHERE IDANAMNESE = ?
");
$stmt->bind_param("i", $idAnamnese);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["sucesso" => false, "erro" => "Anamnese não encontrada."]);
    exit;
}
$response['anamnese'] = $result->fetch_assoc();
$stmt->close();

// Buscar PROCEDIMENTOS PRESCRITOS com nome
$stmtProc = $conn->prepare("
    SELECT P.IDPROCPRESC, P.ID_PROCED, PR.NOMEPROCED, P.PROCEDQTD, P.IMAGEMPROC, P.ORIENTACAO
    FROM PROCPRESC P
    LEFT JOIN PROCEDIMENTO PR ON P.ID_PROCED = PR.IDPROCED
    WHERE P.ID_ANAMNESE = ?
");
$stmtProc->bind_param("i", $idAnamnese);
$stmtProc->execute();
$resultProc = $stmtProc->get_result();
$response['procedimentos'] = [];
while ($row = $resultProc->fetch_assoc()) {
    $response['procedimentos'][] = $row;
}
$stmtProc->close();

// Buscar EXERCÍCIOS PRESCRITOS com nome
$stmtEx = $conn->prepare("
    SELECT E.IDEXERCPRESC, E.ID_EXERCICIO, EX.NOMEEXERCICIO, E.QTDEXERC, E.ORIENTACAO
    FROM EXERCPRESC E
    LEFT JOIN EXERCICIO EX ON E.ID_EXERCICIO = EX.IDEXERCICIO
    WHERE E.ID_ANAMNESE = ?
");
$stmtEx->bind_param("i", $idAnamnese);
$stmtEx->execute();
$resultEx = $stmtEx->get_result();
$response['exercicios'] = [];
while ($row = $resultEx->fetch_assoc()) {
    $response['exercicios'][] = $row;
}
$stmtEx->close();

// Buscar perguntas usadas nas respostas dessa anamnese
$stmtPerg = $conn->prepare("
    SELECT DISTINCT P.IDPERGUNTA, P.PERGUNTA, P.TIPO
    FROM RESPOSTA R
    JOIN PERGUNTAS P ON R.ID_PERGUNTA = P.IDPERGUNTA
    WHERE R.ID_ANAMNESE = ?
");
$stmtPerg->bind_param("i", $idAnamnese);
$stmtPerg->execute();
$resultPerg = $stmtPerg->get_result();
$response['perguntas'] = [];
while ($row = $resultPerg->fetch_assoc()) {
    $response['perguntas'][] = $row;
}
$stmtPerg->close();

// Buscar respostas da anamnese
$stmtResp = $conn->prepare("
    SELECT IDRESPOSTA, ID_PERGUNTA, ID_ANAMNESE, RESPSUBJET, RESPOBJET
    FROM RESPOSTA
    WHERE ID_ANAMNESE = ?
");
$stmtResp->bind_param("i", $idAnamnese);
$stmtResp->execute();
$resultResp = $stmtResp->get_result();
$response['respostas'] = [];
while ($row = $resultResp->fetch_assoc()) {
    $response['respostas'][] = $row;
}
$stmtResp->close();

// Retorno final
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
