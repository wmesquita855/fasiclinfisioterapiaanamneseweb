<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

require_once "conexao.php";

if (!isset($_GET['idModulo'])) {
    echo json_encode(["erro" => "Parâmetro 'idModulo' é obrigatório."]);
    exit;
}

$idModulo = intval($_GET['idModulo']); // segurança: força a ser número

$sql = "
    SELECT P.IDPERGUNTA, P.PERGUNTA, P.TIPO
    FROM PERGUNTA P
    INNER JOIN PERGMODU PM ON PM.ID_PERGUNTA = P.IDPERGUNTA
    WHERE PM.ID_MODULO = $idModulo
    ORDER BY P.IDPERGUNTA
";

$res = $conn->query($sql);

if ($res) {
    $dados = $res->fetch_all(MYSQLI_ASSOC);
    if (empty($dados)) {
        echo json_encode(["mensagem" => "Nenhuma pergunta encontrada para o módulo $idModulo."]);
    } else {
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(["erro" => "Erro na consulta SQL: " . $conn->error]);
}
?>
