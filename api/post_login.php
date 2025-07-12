<?php
// Cabeçalhos CORS para permitir requisições do navegador
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');

require_once "conexao.php";

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['usuario']) || !isset($input['senha'])) {
    echo json_encode(["sucesso" => false, "erro" => "Campos obrigatórios ausentes."]);
    exit;
}

$usuario = $input['usuario'];
$senha = $input['senha'];

$sql = "SELECT 
            U.IDUSUARIO, 
            U.ID_PROFISSIO, 
            U.ID_PESSOAFIS, 
            U.LOGUSUARIO, 
            U.SENHAUSUA,
            PF.NOMEPESSOA 
        FROM USUARIO U
        INNER JOIN PROFISSIONAL P ON P.IDPROFISSIO = U.ID_PROFISSIO
        INNER JOIN PESSOAFIS PF ON PF.IDPESSOAFIS = P.ID_PESSOAFIS
        WHERE U.LOGUSUARIO = ? AND U.SENHAUSUA = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $senha);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $dados = $result->fetch_assoc();
    echo json_encode([
        "sucesso" => true,
        "id_usuario" => (int)$dados["IDUSUARIO"],
        "id_profissional" => (int)$dados["ID_PROFISSIO"],
        "id_pessoafis" => (int)$dados["ID_PESSOAFIS"],
        "logusuario" => $dados["LOGUSUARIO"],
        "senhausua" => $dados["SENHAUSUA"],
        "nome_profissional" => $dados["NOMEPESSOA"]
    ]);
} else {
    echo json_encode(["sucesso" => false, "erro" => "Usuário ou senha inválidos."]);
}
?>
