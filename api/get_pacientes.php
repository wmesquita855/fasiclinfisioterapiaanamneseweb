<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

require_once "conexao.php"; // arquivo de conexão com o banco

$sql = "
    SELECT PF.IDPESSOAFIS, PF.NOMEPESSOA, PF.CPFPESSOA
    FROM PESSOAFIS PF
    INNER JOIN PACIENTE PC ON PC.ID_PESSOAFIS = PF.IDPESSOAFIS
    WHERE PC.STATUSPAC = 1
    ORDER BY PF.NOMEPESSOA
";


$res = $conn->query($sql);

if ($res) {
    $dados = $res->fetch_all(MYSQLI_ASSOC);

    if (empty($dados)) {
        echo json_encode(["mensagem" => "Nenhum paciente encontrado."]);
    } else {
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(["erro" => "Erro na consulta: " . $conn->error]);
}
?>
