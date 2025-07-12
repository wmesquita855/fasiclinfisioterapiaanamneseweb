<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');

include 'conexao.php';

$response = ['sucesso' => false, 'mensagem' => ''];

try {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!$dados || !isset($dados['id_exercpresc']) || !isset($dados['datahora'])) {
        throw new Exception("Parâmetros 'id_exercpresc' e 'datahora' são obrigatórios.");
    }

    $idExercPresc = intval($dados['id_exercpresc']);
    $dataHora = $dados['datahora'];
    $observacao = isset($dados['observacao']) ? $dados['observacao'] : ''; // mesmo que não use, armazena

    if ($idExercPresc <= 0 || empty($dataHora)) {
        throw new Exception("Dados inválidos.");
    }

    $sql = "INSERT INTO EXERCREALIZADO (ID_EXERCPRESC, DATAHORA, OBSERVACAO) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $idExercPresc, $dataHora, $observacao);

    if ($stmt->execute()) {
        $response['sucesso'] = true;
        $response['mensagem'] = "Exercício realizado registrado com sucesso.";
        $response['id_api'] = $conn->insert_id;
    } else {
        throw new Exception("Erro ao inserir no banco: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
