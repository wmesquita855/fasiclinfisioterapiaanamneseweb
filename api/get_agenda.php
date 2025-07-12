<?php
// Cabeçalhos CORS para permitir requisições de qualquer origem
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header('Content-Type: application/json');

require_once "conexao.php";

if (isset($_GET['idProf'])) {
    $id = intval($_GET['idProf']);

    $sql = ($id === 0)
        ? "SELECT 
                A.IDAGENDA,
                DATE_FORMAT(A.DATAABERT, '%Y-%m-%d %H:%i:%s') AS DATAABERT,
                A.SITUAGEN,
                PF.IDPESSOAFIS AS ID_PESSOAFIS,
                PF.CPFPESSOA AS cpf_paciente,
                PF.NOMEPESSOA AS nome_paciente,
                PF.SEXOPESSOA AS sexo_paciente,
                PRF.IDPROFISSIO AS ID_PROFISSIO,
                PFP.CPFPESSOA AS cpf_profissional,
                PFP.NOMEPESSOA AS nome_profissional,
                PR.IDPROCED AS ID_PROCED,
                PR.CODPROCED AS código_procedimento,
                PR.DESCRPROC AS nome_procedimento
            FROM AGENDA A
            INNER JOIN PACIENTE P ON P.ID_PESSOAFIS = A.ID_PESSOAFIS
            INNER JOIN PESSOAFIS PF ON PF.IDPESSOAFIS = P.ID_PESSOAFIS
            INNER JOIN PROCEDIMENTO PR ON PR.IDPROCED = A.ID_PROCED
            INNER JOIN PROFISSIONAL PRF ON PRF.IDPROFISSIO = A.ID_PROFISSIO
            INNER JOIN PESSOAFIS PFP ON PFP.IDPESSOAFIS = PRF.ID_PESSOAFIS
            ORDER BY A.DATAABERT DESC"
        : "SELECT 
                A.IDAGENDA,
                DATE_FORMAT(A.DATAABERT, '%Y-%m-%d %H:%i:%s') AS DATAABERT,
                A.SITUAGEN,
                PF.IDPESSOAFIS AS ID_PESSOAFIS,
                PF.CPFPESSOA AS cpf_paciente,
                PF.NOMEPESSOA AS nome_paciente,
                PF.SEXOPESSOA AS sexo_paciente,
                PRF.IDPROFISSIO AS ID_PROFISSIO,
                PFP.CPFPESSOA AS cpf_profissional,
                PFP.NOMEPESSOA AS nome_profissional,
                PR.IDPROCED AS ID_PROCED,
                PR.CODPROCED AS código_procedimento,
                PR.DESCRPROC AS nome_procedimento
            FROM AGENDA A
            INNER JOIN PACIENTE P ON P.ID_PESSOAFIS = A.ID_PESSOAFIS
            INNER JOIN PESSOAFIS PF ON PF.IDPESSOAFIS = P.ID_PESSOAFIS
            INNER JOIN PROCEDIMENTO PR ON PR.IDPROCED = A.ID_PROCED
            INNER JOIN PROFISSIONAL PRF ON PRF.IDPROFISSIO = A.ID_PROFISSIO
            INNER JOIN PESSOAFIS PFP ON PFP.IDPESSOAFIS = PRF.ID_PESSOAFIS
            WHERE A.ID_PROFISSIO = $id
            ORDER BY A.DATAABERT DESC";

    $res = $conn->query($sql);

    if ($res) {
        $dados = $res->fetch_all(MYSQLI_ASSOC);
        echo json_encode($dados);
    } else {
        echo json_encode(["erro" => "Erro na consulta SQL: " . $conn->error]);
    }
} else {
    echo json_encode(["erro" => "Parâmetro 'idProf' não informado."]);
}
?>
