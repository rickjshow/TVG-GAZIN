<?php

include "conexao.php";


if(isset($_GET['idGS'])) {

    $idSessao = $_GET['idGS'];

    $delete = "DELETE FROM sessoes WHERE id = :id";
    $delete1 = $pdo->prepare($delete);
    $delete1->bindValue(":id", $idSessao);

    if($delete1->execute()){
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Sessão excluida com sucesso!');
        header("location: novaEdicao.php");
        exit();
    }
    
} 


?>
