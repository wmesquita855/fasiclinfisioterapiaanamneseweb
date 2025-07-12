<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

require_once "conexao.php";

// Consulta para buscar profissionais com ID_ESPEC = 6
$sql = "
    SELECT 
        PR.IDPROFISSIO,
        PF.NOMEPESSOA
    FROM PROFISSIONAL PR
    INNER JOIN PROFI_ESPEC PE ON PE.ID_PROFISSIO = PR.IDPROFISSIO
    INNER JOIN PESSOAFIS PF ON PF.IDPESSOAFIS = PR.ID_PESSOAFIS
    WHERE PE.ID_ESPEC = 6
";

$res = $conn->query($sql);

if ($res) {
    $dados = $res->fetch_all(MYSQLI_ASSOC);
    if (empty($dados)) {
        echo json_encode(["mensagem" => "Nenhum profissional com especialidade 6 encontrado."]);
    } else {
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(["erro" => "Erro na consulta SQL: " . $conn->error]);
}
?>
