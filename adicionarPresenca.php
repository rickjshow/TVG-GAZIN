<?php

require_once "conexao.php";

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

    $querySessao = "SELECT s.id AS id_sessao, s.situacao, u.id AS user, gs.id_equipe AS equipe FROM sessoes AS s
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

        $idUser = $resultSessao['user'];

        $idEquipe = $resultSessao['equipe'];

        $presencas = $_POST['presenca'];

        $queryInsert = "INSERT INTO presenca (id_sessao, id_participantes, id_status, id_user, id_equipe) VALUES (:id_sessao, :id_participante, :id_status, :id_user, :id_equipe)";
        $stmtInsert = $pdo->prepare($queryInsert);

        foreach ($presencas as $participante => $status) {
            $queryPart = "SELECT id FROM participantes WHERE nome = :participante";
            $stmtPart = $pdo->prepare($queryPart);
            $stmtPart->bindParam(":participante", $participante);
            $stmtPart->execute();
            $resultPart = $stmtPart->fetch(PDO::FETCH_ASSOC);

            if ($resultPart) {
                $idParticipante = $resultPart['id'];

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
                    $stmtInsert->bindParam(":id_user", $idUser);
                    $stmtInsert->bindParam(":id_equipe", $idEquipe);
                    $stmtInsert->execute();
                } else {
                    session_start();
                    $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Presença ja cadastrada para os participantes!');
                    header("location: presenca.php");
                    exit();
                }
            }
        }
        if($stmtInsert){
            session_start();
            $_SESSION['alertaSucesso'] = array('tipo' => 'success', 'mensagem' => 'Presença cadastrada com sucesso!');
            header("location: presenca.php");
            exit();
        }
    } else {
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Sessão não encontrada ou não está pendente!');
        header("location: presenca.php");
        exit();
    }
    exit();
}
?>