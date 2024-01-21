<?php

require_once "conexao.php";
require_once "presenca.php";

if (!function_exists('obterIdStatus')) {
    function obterIdStatus($nomeStatus)
    {
        global $pdo;
        $queryStatus = "SELECT id FROM status WHERE nome = :nomeStatus";
        $stmtStatus = $pdo->prepare($queryStatus);
        $stmtStatus->bindParam(":nomeStatus", $nomeStatus);
        $stmtStatus->execute();
        $resultStatus = $stmtStatus->fetch(PDO::FETCH_ASSOC);
        return ($resultStatus) ? $resultStatus['id'] : null;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionarPresenca'])) {

    $username = $_SESSION['username'];

    $querySessao = "SELECT s.id AS id_sessao, s.situacao
                    FROM sessoes AS s
                    JOIN gerenciamento_sessao AS gs ON s.id = gs.id_sessoes
                    JOIN usuarios AS u ON gs.id_usuarios = u.id
                    WHERE u.nome = :username
                    ORDER BY s.data_criacao DESC
                    LIMIT 1";

    $stmtSessao = $pdo->prepare($querySessao);
    $stmtSessao->bindParam(":username", $username);
    $stmtSessao->execute();

    $resultSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);

    if ($resultSessao && $resultSessao['situacao'] == 'Pendente') {

        $idSessao = $resultSessao['id_sessao'];

        $presencas = $_POST['presenca'];

        $queryInsert = "INSERT INTO presenca (id_sessao, id_participantes, id_status) VALUES (:id_sessao, :id_participante, :id_status)";
        $stmtInsert = $pdo->prepare($queryInsert);

        foreach ($presencas as $participante => $status) {
            $queryPart = "SELECT id FROM participantes WHERE nome = :participante";
            $stmtPart = $pdo->prepare($queryPart);
            $stmtPart->bindParam(":participante", $participante);
            $stmtPart->execute();
            $resultPart = $stmtPart->fetch(PDO::FETCH_ASSOC);

            if ($resultPart) {
                $idParticipante = $resultPart['id'];

                // Verifica se a presença já foi registrada para o participante nesta sessão
                $queryVerificaPresenca = "SELECT COUNT(*) FROM presenca WHERE id_sessao = :id_sessao AND id_participantes = :id_participante";
                $stmtVerificaPresenca = $pdo->prepare($queryVerificaPresenca);
                $stmtVerificaPresenca->bindParam(":id_sessao", $idSessao);
                $stmtVerificaPresenca->bindParam(":id_participante", $idParticipante);
                $stmtVerificaPresenca->execute();
                $numPresencas = $stmtVerificaPresenca->fetchColumn();

                if ($numPresencas == 0) {
                    $idStatus = ($status == 'Presente') ? obterIdStatus('Presente') : obterIdStatus('Ausente');

                    $stmtInsert->bindParam(":id_sessao", $idSessao);
                    $stmtInsert->bindParam(":id_participante", $idParticipante);
                    $stmtInsert->bindParam(":id_status", $idStatus);
                    $stmtInsert->execute();
                } else {
                    echo "<script>alert('Presença já cadastrada para $participante nesta sessão!'); window.location.href='home.php';</script>";
                }
            }
        }

        echo "<script>alert('Presença cadastrada com sucesso!'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Sessão não encontrada ou não está pendente.'); window.location.href='presenca.php';</script>";
    }

    exit();
}
?>

