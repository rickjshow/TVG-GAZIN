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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionarRascunho'])) {
    
    $username = $_SESSION['username'];

    $querySessao = "SELECT s.id AS id_sessao, gs.id_equipe AS equipe, s.situacao, u.id AS userId FROM sessoes AS s
                    JOIN gerenciamento_sessao AS gs ON s.id = gs.id_sessoes
                    JOIN usuarios AS u ON gs.id_usuarios = u.id
                    WHERE u.nome = :username
                    ORDER BY s.data_criacao DESC
                    LIMIT 1";

    $stmtSessao = $pdo->prepare($querySessao);
    $stmtSessao->bindParam(":username", $username);
    $stmtSessao->execute();

    $resultSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);

    $userId = $resultSessao['userId'];

    $equipeId = $resultSessao['equipe'];

    $idSessao = $resultSessao['id_sessao'];

    if ($resultSessao && $resultSessao['situacao'] == 'Pendente') {

        $rascunho = $_POST['presenca'];

        $queryInsert = "INSERT INTO rascunho_presenca (id_sessao, id_participantes, id_status, id_user, id_equipe) VALUES (:id_sessao, :id_participante, :id_status, :id_user, :id_equipe)";
        $stmtUpdate = $pdo->prepare($queryInsert);

        foreach ($rascunho as $participante => $status) {
            $queryPart = "SELECT id FROM participantes WHERE nome = :participante";
            $stmtPart = $pdo->prepare($queryPart);
            $stmtPart->bindParam(":participante", $participante);
            $stmtPart->execute();
            $resultPart = $stmtPart->fetch(PDO::FETCH_ASSOC);  
            
            $idParticipante = $resultPart['id'];
            
            $idStatus = ($status == 'Presente') ? obterIdStatus('Presente') : obterIdStatus('Ausente');
            
            $queryInsert = "INSERT INTO rascunho_presenca (id_sessao, id_participantes, id_status, id_user, id_equipe) VALUES (:id_sessao, :id_participante, :id_status, :id_user, :id_equipe)";
            $stmtUpdate = $pdo->prepare($queryInsert);
            
            $stmtUpdate->bindParam(":id_sessao", $idSessao);
            $stmtUpdate->bindParam(":id_participante", $idParticipante);
            $stmtUpdate->bindParam(":id_status", $idStatus);
            $stmtUpdate->bindParam(":id_user", $userId);
            $stmtUpdate->bindParam(":id_equipe", $equipeId);
            
            if (!$stmtUpdate->execute()) {
                session_start();
                $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro na inserção do rascunho!');
                header("location: home.php");
                exit();
            }
        }
        
        session_start();
        $_SESSION['alertaSucesso'] = array('tipo' => 'success', 'mensagem' => 'Rascunho inserido com sucesso!');
        header("location: rascunhoPresenca.php");
        exit();
        
        
    } else {
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Sessão não encontrada ou não está pendente!');
        header("location: home.php");
        exit();
    }
}

?>