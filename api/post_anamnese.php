<?php
header('Content-Type: application/json');
require_once "conexao.php";


$dados = json_decode(file_get_contents("php://input"), true);

// Coleta e validação dos campos
$ID_PACIENTE  = intval($dados['ID_PACIENTE']);
$ID_PROFISSIO = intval($dados['ID_PROFISSIO']);
$DATAANAM     = $dados['DATAANAM'];       // 'YYYY-MM-DD HH:MM:SS'
$NOMERESP     = $dados['NOMERESP'];
$CPFRESP      = $dados['CPFRESP'];
$AUTVISIB     = intval($dados['AUTVISIB']);     // 0 ou 1 (booleano)
$STATUSANM    = intval($dados['STATUSANM']);    // 0 ou 1
$STATUSFUNC   = intval($dados['STATUSFUNC']);   // 0 ou 1
$OBSERVACOES  = $dados['OBSERVACOES'];

// Comando SQL (IDANAMNESE é auto_incrementado)
$stmt = $conn->prepare("
    INSERT INTO ANAMNESE 
    (ID_PACIENTE, ID_PROFISSIO, DATAANAM, NOMERESP, CPFRESP, AUTVISIB, STATUSANM, STATUSFUNC, OBSERVACOES)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iisssiiis",
    $ID_PACIENTE,
    $ID_PROFISSIO,
    $DATAANAM,
    $NOMERESP,
    $CPFRESP,
    $AUTVISIB,
    $STATUSANM,
    $STATUSFUNC,
    $OBSERVACOES
);

if ($ok) {
    $idGerado = $conn->insert_id;
    echo json_encode(["sucesso" => true, "IDANAMNESE" => $idGerado]);
} else {
    echo json_encode(["sucesso" => false, "erro" => $stmt->error]);
}
?>
