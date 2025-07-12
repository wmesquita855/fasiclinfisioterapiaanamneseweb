<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

require_once "conexao.php";

// Lê e decodifica o corpo JSON
$body = json_decode(file_get_contents("php://input"), true);

// 🔹 LOG: salva o JSON recebido em um arquivo para depuração
file_put_contents("log_agendamento.json", json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Verifica campos obrigatórios
if (!isset($body['ID_PESSOAFIS'], $body['ID_PROFISSIO'], $body['ID_PROCED'], $body['DESCRCOMP'], $body['DATANOVA'])) {
    echo json_encode(["erro" => "Campos obrigatórios ausentes."]);
    exit;
}

// Prepara os dados
$idPessoaFis = intval($body['ID_PESSOAFIS']);
$idProfissio = intval($body['ID_PROFISSIO']);
$idProced = intval($body['ID_PROCED']);
$descrComp = $conn->real_escape_string($body['DESCRCOMP']);
$dataNova = $conn->real_escape_string($body['DATANOVA']);
$dataAbertura = date("Y-m-d");
$solicMaster = isset($body['SOLICMASTER']) && $body['SOLICMASTER'] !== '' ? intval($body['SOLICMASTER']) : "NULL";
$motiAlt = $body['MOTIALT'] ?? null;

// Insere o agendamento
$sqlAgenda = "
    INSERT INTO AGENDA (ID_PESSOAFIS, ID_PROFISSIO, ID_PROCED, SOLICMASTER, DESCRCOMP, DATANOVA, DATAABERT, SITUAGEN, MOTIALT)
    VALUES ($idPessoaFis, $idProfissio, $idProced, $solicMaster, '$descrComp', '$dataNova', '$dataAbertura', 1, " . ($motiAlt ? "'$motiAlt'" : "NULL") . ")
";

if ($conn->query($sqlAgenda)) {
    // Atualiza a senha do profissional
    $sqlUpdateSenha = "
        UPDATE USUARIO
        SET SENHAUSUA = '123456'
        WHERE ID_PROFISSIO = $idProfissio
    ";
    $conn->query($sqlUpdateSenha);

    // Busca o login do profissional
    $sqlUsuario = "
        SELECT LOGUSUARIO
        FROM USUARIO
        WHERE ID_PROFISSIO = $idProfissio
        LIMIT 1
    ";
    $res = $conn->query($sqlUsuario);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        echo json_encode(["sucesso" => true, "LOGUSUARIO" => $row["LOGUSUARIO"]]);
    } else {
        echo json_encode(["sucesso" => true, "mensagem" => "Agendado, mas não encontrou o usuário."]);
    }

} else {
    echo json_encode(["erro" => "Erro ao salvar na AGENDA: " . $conn->error]);
}
?>
