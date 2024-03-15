<?php

    require_once "conexao.php";
    include "permissao.php";

    verificarPermissao($permission);

    if(isset($_GET['idEquipe']) && isset($_GET['idSessao'])){
        $idEquipe = $_GET['idEquipe'];
        $idSessao = $_GET['idSessao'];

        $queryExclusao = "DELETE FROM gerenciamento_sessao WHERE id_equipe = ? AND id_sessoes = ?";
        $result = $pdo->prepare($queryExclusao);
        $result->bindValue(1, $idEquipe);
        $result->bindValue(2, $idSessao);
        $result->execute();

        $queryExclusaoProva = "DELETE FROM equipes_provas WHERE id_equipes = ? AND id_sessao = ?";
        $result2 = $pdo->prepare($queryExclusaoProva);
        $result2->bindValue(1, $idEquipe);
        $result2->bindValue(2, $idSessao);
        $result2->execute();

        if($result2){
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Equipe Excluida com sucesso!');
            header("location: novaEdicao.php");
            exit();
        }else{
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Não foi possível excluir a equipe!');
            header("location: novaEdicao.php");
            exit();
        }
    }