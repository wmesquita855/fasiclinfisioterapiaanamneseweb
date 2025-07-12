<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("conexao.php");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['ID_PROFISSIO'])) {
    echo json_encode(["sucesso" => false, "erro" => "ID_PROFISSIO não informado."]);
    exit;
}

$idProf = intval($input['ID_PROFISSIO']);
$response = ["sucesso" => true, "pacientes" => []];

// Buscar anamneses do profissional com especialidade 6
$query = "
SELECT 
  PF.IDPESSOAFIS, PF.CPFPESSOA, PF.NOMEPESSOA, PF.DATANASCPES, PF.SEXOPESSOA,
  A.IDANAMNESE, A.DATAANAM, A.NOMERESP, A.CPFRESP, A.OBSERVACOES,
  PAC.IDPACIENTE
FROM PESSOAFIS PF
JOIN PACIENTE PAC ON PAC.ID_PESSOAFIS = PF.IDPESSOAFIS
JOIN ANAMNESE A ON A.ID_PACIENTE = PAC.IDPACIENTE
JOIN PROCPRESC P ON P.ID_ANAMNESE = A.IDANAMNESE
JOIN ESPECPROCED E ON E.ID_PROCED = P.ID_PROCED AND E.ID_ESPEC = 6
WHERE A.ID_PROFISSIO = ?
GROUP BY A.IDANAMNESE
ORDER BY A.DATAANAM DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idProf);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $idAnamnese = $row['IDANAMNESE'];
    $idPaciente = $row['IDPACIENTE'];

    // Respostas da anamnese
    $respostas = [];
    $stmtResp = $conn->prepare("
        SELECT R.IDRESPOSTA, R.ID_PERGUNTA, P.PERGUNTA, P.TIPO, R.RESPSUBJET, R.RESPOBJET
        FROM RESPOSTA R
        JOIN PERGUNTA P ON P.IDPERGUNTA = R.ID_PERGUNTA
        WHERE R.ID_ANAMNESE = ?
    ");
    $stmtResp->bind_param("i", $idAnamnese);
    $stmtResp->execute();
    $resResp = $stmtResp->get_result();
    while ($resp = $resResp->fetch_assoc()) {
        $respostas[] = $resp;
    }
    $stmtResp->close();

    // Procedimentos prescritos
    $proceds = [];
    $stmtProc = $conn->prepare("
        SELECT PP.IDPROCPRESC, PP.ID_PROCED, P.DESCRPROC, PP.PROCEDQTD, PP.ORIENTACAO
        FROM PROCPRESC PP
        JOIN PROCEDIMENTO P ON P.IDPROCED = PP.ID_PROCED
        WHERE PP.ID_ANAMNESE = ?
    ");
    $stmtProc->bind_param("i", $idAnamnese);
    $stmtProc->execute();
    $resProc = $stmtProc->get_result();
    while ($proc = $resProc->fetch_assoc()) {
        $proceds[] = $proc;
    }
    $stmtProc->close();

    // Exercícios prescritos
    $exercs = [];
    $stmtEx = $conn->prepare("
        SELECT EP.IDEXERCPRESC, EP.ID_EXERCICIO, E.DESCREXERC, E.LINKVIDEO, EP.QTDEXERC, EP.ORIENTACAO
        FROM EXERCPRESC EP
        JOIN EXERCICIO E ON E.IDEXERCICIO = EP.ID_EXERCICIO
        WHERE EP.ID_ANAMNESE = ?
    ");
    $stmtEx->bind_param("i", $idAnamnese);
    $stmtEx->execute();
    $resEx = $stmtEx->get_result();
    while ($ex = $resEx->fetch_assoc()) {
        $exercs[] = $ex;
    }
    $stmtEx->close();

    // Prontuários do paciente com especialidade 6
    $prontuarios = [];
    $stmtPront = $conn->prepare("
        SELECT IDPRONTU, DATAPROCED, DESCRPRONTU, LINKPROCED, AUTOPACVISU
        FROM PRONTUARIO
        WHERE ID_PACIENTE = ? AND ID_ESPEC = 6
        ORDER BY DATAPROCED DESC
    ");
    $stmtPront->bind_param("i", $idPaciente);
    $stmtPront->execute();
    $resPront = $stmtPront->get_result();
    while ($pront = $resPront->fetch_assoc()) {
        $prontuarios[] = $pront;
    }
    $stmtPront->close();

    $response["pacientes"][] = [
        "dados" => [
            "cpf" => $row["CPFPESSOA"],
            "nome" => $row["NOMEPESSOA"],
            "dataNascimento" => $row["DATANASCPES"],
            "sexo" => $row["SEXOPESSOA"]
        ],
        "anamnese" => [
            "id" => $row["IDANAMNESE"],
            "data" => $row["DATAANAM"],
            "nomeResp" => $row["NOMERESP"],
            "cpfResp" => $row["CPFRESP"],
            "observacoes" => $row["OBSERVACOES"]
        ],
        "respostas" => $respostas,
        "procedimentos" => $proceds,
        "exercicios" => $exercs,
        "prontuarios" => $prontuarios
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
