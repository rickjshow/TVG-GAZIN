<?php

    require_once('conexao.php');
    include 'permissao.php';

    verificarPermissao($permission);

    if(isset($_GET['idUsuario'])){
        $id = $_GET['idUsuario'];

        $query = "SELECT permission FROM usuarios WHERE id = ?";
        $result = $pdo->prepare($query);
        $result->bindValue(1, $id);
        $result->execute();
        $permission = $result->fetchColumn();

        if($permission == 'limited'){
            $senha = 'facilitadorgazin';
        }elseif($permission == 'admin'){
            $senha = 'admingazin';
        }

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $queryUpdate = "UPDATE usuarios SET senha = ?, senha_resetada = 'sim' WHERE id = ?";
        $result2 = $pdo->prepare($queryUpdate);
        $result2->bindValue(1, $hash);
        $result2->bindValue(2, $id);
        $result2->execute();

        if($result2){
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Senha resetada com sucesso!');
            header("location: acesso.php");
            exit();
        }else{
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Falha ao resetar a senha!');
            header("location: acesso.php");
            exit();
        }
    }