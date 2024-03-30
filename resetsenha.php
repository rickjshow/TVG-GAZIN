<?php

    require_once('conexao.php');
    include 'permissao.php';

    verificarPermissao($permission);

    if(isset($_GET['idUsuario'])){
        $id = $_GET['idUsuario'];

        $query = "SELECT permission, nome FROM usuarios WHERE id = ?";
        $result = $pdo->prepare($query);
        $result->bindValue(1, $id);
        $result->execute();
        $data = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach($data as $row){
            $permission = $row['permission'];
            $nome = $row['nome'];
        }

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

            $user = $_SESSION['username'];

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }
            
            $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

            $insert = "INSERT INTO log_facilitadores (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?, 'Senha resetada do usuário - $nome' , NOW() , NULL ,NULL)";
            $stmt = $pdo->prepare($insert);
            $stmt->bindValue(1, $user);
            $stmt->bindValue(2, $ip_user);
            $stmt->execute();

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