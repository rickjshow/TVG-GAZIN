<?php

include "conexao.php";


if(isset($_GET['idGS'])) {

    $idSessao = $_GET['idGS'];

    $delete = "DELETE FROM sessoes WHERE id = :id";
    $delete1 = $pdo->prepare($delete);
    $delete1->bindValue(":id", $idSessao);

    if($delete1->execute()){

        $query = "SELECT nome FROM sessoes WHERE id = ?";
            $result = $pdo->prepare($query);
            $result->bindValue(1, $idSessao);
            $result->execute();
            $name = $result->fetchColumn();

            $user = $_SESSION['username'];

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }
            
            $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

            $querySessao = "SELECT data_TVG FROM sessoes WHERE id = ?";
            $resultado = $pdo->prepare($querySessao);
            $resultado->bindValue(1, $idSessao);
            $resultado->execute();
            $data = $resultado->fetchColumn();

            $insert = "INSERT INTO log_sessoes (sessao, data_sessao, usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?,?,?, 'exclusão de sessão' , NOW() , NULL ,?)";
            $stmt = $pdo->prepare($insert);
            $stmt->bindValue(1, $name);
            $stmt->bindValue(2, $data);
            $stmt->bindValue(3, $user);
            $stmt->bindValue(4, $ip_user);
            $stmt->bindValue(5, $nome);
            $stmt->execute();

        $querySituacao = "UPDATE usuarios SET situacao = 'Inativo' WHERE permission = 'limited'";
        $stmt2 = $pdo->prepare( $querySituacao);
        $stmt2->execute();

        session_start();
        $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Sessão excluida com sucesso!');
        header("location: novaEdicao.php");
        exit();
    }
    
}

?>