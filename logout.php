<?php 

include "conexao.php";

session_start();

    $username = $_SESSION['username'];

    $queryUser = "SELECT id, permission FROM usuarios WHERE nome = :username";
    $stmtUser = $pdo->prepare($queryUser);
    $stmtUser->bindParam(":username", $username);
    $stmtUser->execute();
    $resultUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $UserId = $resultUser['id'];

    $query = "SELECT situacao FROM usuarios WHERE id = :usuario_id";
    $consulta = $pdo->prepare($query);
    $consulta->bindParam(':usuario_id', $UserId);
    $consulta->execute();
    $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

    if($resultado['situacao'] == 'Ativo'){

        session_unset();

        session_destroy();

        header("location:index.php");
        exit();
    }else{

        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'O seu usuário foi inativado!');
        header("location: index.php");
        exit();
    }