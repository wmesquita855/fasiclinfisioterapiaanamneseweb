<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "conexao.php";
///require_once "conexao2.php"; // conexão exclusiva para EXERCPRESC e TOKEN

$dados = json_decode(file_get_contents("php://input"), true);
$observacoesExtra = isset($_POST['OBSERVACOES']) ? $_POST['OBSERVACOES'] : null;

// Log do JSON recebido
$dataHora = date("Ymd_His");
//$nomeArquivo = "json_recebido_respostas_$dataHora.log";
//file_put_contents($nomeArquivo, json_encode($dados, JSON_PRETTY_PRINT));

//$logName = "chamada_api_" . date("Ymd_His") . "_" . rand(1000, 9999) . ".log";
//file_put_contents($logName, file_get_contents("php://input"));


if (!$dados || !isset($dados['anamnese']) || !isset($dados['respostas'])) {
    echo json_encode(["sucesso" => false, "erro" => "Dados incompletos."]);
    exit;
}

$anamnese = $dados['anamnese'];
$tokenRecebido = isset($dados['token']) ? $dados['token'] : null;
$idPessoaFis = $anamnese['ID_PESSOAFIS'];

if ($observacoesExtra !== null) {
    $anamnese['OBSERVACOES'] = $observacoesExtra;
}

// Buscar IDPACIENTE com base no ID_PESSOAFIS
$stmtBusca = $conn->prepare("SELECT IDPACIENTE FROM PACIENTE WHERE ID_PESSOAFIS = ?");
$stmtBusca->bind_param("i", $idPessoaFis);
$stmtBusca->execute();
$stmtBusca->bind_result($idPaciente);
$stmtBusca->fetch();
$stmtBusca->close();

if (!$idPaciente) {
    echo json_encode(["sucesso" => false, "erro" => "Paciente não encontrado para ID_PESSOAFIS = $idPessoaFis"]);
    exit;
}

// Inserir ANAMNESE
$stmt = $conn->prepare("
    INSERT INTO ANAMNESE 
    (ID_PACIENTE, ID_PROFISSIO, DATAANAM, NOMERESP, CPFRESP, AUTVISIB, STATUSANM, STATUSFUNC, OBSERVACOES)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    exit;
}

$stmt->bind_param(
    "iisssiiis",
    $idPaciente,
    $anamnese['ID_PROFISSIO'],
    $anamnese['DATAANAM'],
    $anamnese['NOMERESP'],
    $anamnese['CPFRESP'],
    $anamnese['AUTVISIB'],
    $anamnese['STATUSANM'],
    $anamnese['STATUSFUNC'],
    $anamnese['OBSERVACOES']
);

if (!$stmt->execute()) {
    echo json_encode(["sucesso" => false, "erro" => $stmt->error]);
    exit;
}

$idAnamnese = $conn->insert_id;
$stmt->close();

$okGeral = true;
$retornoRespostas = [];
$retornoProcedimentos = [];
$retornoExercicios = [];

// Inserir RESPOSTAS
$stmtResp = $conn->prepare("
    INSERT INTO RESPOSTA (ID_PERGUNTA, ID_ANAMNESE, RESPSUBJET, RESPOBJET)
    VALUES (?, ?, ?, ?)
");

if (!$stmtResp) {
    echo json_encode(["sucesso" => false, "erro" => $conn->error]);
    exit;
}

foreach ($dados['respostas'] as $resposta) {
    $idPergunta = intval($resposta['ID_PERGUNTA']);
    $respsubjet = isset($resposta['RESPSUBJET']) ? $resposta['RESPSUBJET'] : null;
    $respobjet = isset($resposta['RESPOBJET']) ? (int)$resposta['RESPOBJET'] : null;

    $stmtResp->bind_param("iiss", $idPergunta, $idAnamnese, $respsubjet, $respobjet);
    if ($stmtResp->execute()) {
        $retornoRespostas[] = [
            "id_app" => $resposta['ID_APP'],
            "id_api" => $conn->insert_id
        ];
    } else {
        $okGeral = false;
        break;
    }
}
$stmtResp->close();

// Inserir PROCEDIMENTOS
if (isset($dados['procedimentos'])) {
    $stmtProc = $conn->prepare("
        INSERT INTO PROCPRESC (ID_ANAMNESE, ID_PROCED, PROCEDQTD, IMAGEMPROC, ORIENTACAO)
        VALUES (?, ?, ?, ?, ?)
    ");
    if (!$stmtProc) {
        echo json_encode(["sucesso" => false, "erro" => $conn->error]);
        exit;
    }

    foreach ($dados['procedimentos'] as $proc) {
        $stmtProc->bind_param(
            "iiiss",
            $idAnamnese,
            $proc['ID_PROCED'],
            $proc['PROCEDQTD'],
            $proc['IMAGEMPROC'],
            $proc['ORIENTACAO']
        );
        if ($stmtProc->execute()) {
            $retornoProcedimentos[] = [
                "id_app" => $proc['ID_APP'],
                "id_api" => $conn->insert_id
            ];
        } else {
            $okGeral = false;
            break;
        }
    }
    $stmtProc->close();
}

// Inserir EXERCÍCIOS
if (isset($dados['exercicios'])) {
    $stmtEx = $conn->prepare("
        INSERT INTO EXERCPRESC (ID_EXERCICIO, ID_ANAMNESE, QTDEXERC, ORIENTACAO)
        VALUES (?, ?, ?, ?)
    ");
    if (!$stmtEx) {
        echo json_encode(["sucesso" => false, "erro" => $conn2->error]);
        exit;
    }

    foreach ($dados['exercicios'] as $ex) {
        $stmtEx->bind_param(
            "iiis",
            $ex['ID_EXERC'],
            $idAnamnese,
            $ex['EXERCQTD'],
            $ex['ORIENTACAO']
        );
        if ($stmtEx->execute()) {
            $retornoExercicios[] = [
                "id_app" => $ex['ID_APP'],
                "id_api" => $conn->insert_id
            ];
        } else {
            $okGeral = false;
            break;
        }
    }
    $stmtEx->close();
}


// Inserir TOKEN recebido
$idToken = null;
if ($tokenRecebido && $idAnamnese) {
    $stmtToken = $conn->prepare("
        INSERT INTO TOKENFISIO (ID_ANAMNESE, TOKEN)
        VALUES (?, ?)
    ");
    if ($stmtToken) {
        $stmtToken->bind_param("is", $idAnamnese, $tokenRecebido);
        if ($stmtToken->execute()) {
            $idToken = $conn->insert_id;
        } else {
            $okGeral = false;
        }
        $stmtToken->close();
    } else {
        $okGeral = false;
    }
}

// Atualizar SITUAGEN na tabela AGENDA
if (isset($anamnese['IDAGENDA'])) {
    $idAgenda = intval($anamnese['IDAGENDA']);
    $stmtAgenda = $conn->prepare("UPDATE AGENDA SET SITUAGEN = 2 WHERE IDAGENDA = ?");
    if ($stmtAgenda) {
        $stmtAgenda->bind_param("i", $idAgenda);
        if (!$stmtAgenda->execute()) {
            $okGeral = false;
        }
        $stmtAgenda->close();
    } else {
        $okGeral = false;
    }
}


// Resposta final
echo json_encode([
    "sucesso" => $okGeral,
    "ID_ANAMNESE" => $idAnamnese,
    "respostas" => $retornoRespostas,
    "procedimentos" => $retornoProcedimentos,
    "exercicios" => $retornoExercicios,
    "token" => [
        "id_token" => $idToken,
        "valor" => $tokenRecebido
    ]
]);
?>
