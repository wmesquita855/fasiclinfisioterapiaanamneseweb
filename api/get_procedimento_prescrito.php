<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

require_once "conexao.php";

$idPessoaFis = isset($_GET['idpessoafis']) ? intval($_GET['idpessoafis']) : 0;

if ($idPessoaFis <= 0) {
    echo json_encode([
        "procedimentos" => [],
        "mensagem" => "Parâmetro 'idpessoafis' inválido ou ausente."
    ]);
    exit;
}

$sql = "SELECT 
            MAX(pp.IDPROCPRESC) AS IDPROCPRESC,
            MAX(pp.ID_ANAMNESE) AS ID_ANAMNESE,
            pp.ID_PROCED,
            MAX(p.DESCRPROC) AS DESCRPROC,
            MAX(pp.PROCEDQTD) AS PROCEDQTD,
            MAX(pp.IMAGEMPROC) AS IMAGEMPROC,
            MAX(pp.ORIENTACAO) AS ORIENTACAO
        FROM PROCPRESC pp
        INNER JOIN ANAMNESE a ON pp.ID_ANAMNESE = a.IDANAMNESE
        INNER JOIN PACIENTE pac ON a.ID_PACIENTE = pac.IDPACIENTE
        INNER JOIN PROCEDIMENTO p ON pp.ID_PROCED = p.IDPROCED
        WHERE pac.ID_PESSOAFIS = ?
        GROUP BY pp.ID_PROCED
        ORDER BY IDPROCPRESC DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idPessoaFis);
$stmt->execute();
$res = $stmt->get_result();

$procedimentos = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$idsExistentes = array_column($procedimentos, 'ID_PROCED');

// Adiciona o procedimento padrão 5967 se ele não estiver na lista
if (!in_array(5967, $idsExistentes)) {
    $sqlExtra = "SELECT IDPROCED AS ID_PROCED, DESCRPROC FROM PROCEDIMENTO WHERE IDPROCED = 5967 LIMIT 1";
    $resExtra = $conn->query($sqlExtra);

    if ($resExtra && $resExtra->num_rows > 0) {
        $row = $resExtra->fetch_assoc();

        $procedimentos[] = [
            "IDPROCPRESC" => null,
            "ID_ANAMNESE" => null,
            "ID_PROCED" => intval($row['ID_PROCED']),
            "DESCRPROC" => $row['DESCRPROC'],
            "PROCEDQTD" => null,
            "IMAGEMPROC" => null,
            "ORIENTACAO" => null
        ];
    }
}

echo json_encode([
    "procedimentos" => $procedimentos,
    "mensagem" => count($procedimentos) > 1 
        ? "Procedimentos encontrados com sucesso." 
        : "Procedimento padrão incluído."
]);
?>
