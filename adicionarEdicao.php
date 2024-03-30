<?php
require_once "conexao.php";
require_once "novaEdicao.php";

if (isset($_POST["add_tvg"])) {
    if (empty($_POST["nometvg"]) || empty($_POST["datatvg"])) {
        echo "<script>alert('Favor inserir todos os dados!'); window.location.href='novaEdicao.php';</script>";
        exit();
    } else {
        $nome = $_POST["nometvg"];
        $data = $_POST["datatvg"];

        date_default_timezone_set('America/Sao_Paulo');

        $dateAtual = date("Y-m-d");

        $query = "SELECT COUNT(*) FROM sessoes WHERE nome = ?";
        $result = $pdo->prepare($query);
        $result->bindValue(1, $nome);
        $result->execute();
        $num = $result->fetchColumn();

        if($num > 0){
            echo "<script>alerta('error', 'Esse nome de TVG já existe!');</script>";
        }

        if($data < $dateAtual){
            echo "<script>alerta('error', 'A data do TVG não pode ser menor do que a data atual!');</script>";
        } else{
            $querySessao = "SELECT * FROM sessoes WHERE situacao = 'Pendente'";
            $consulta1 = $pdo->prepare($querySessao);
            $consulta1->execute();
    
            if ($consulta1->rowCount() > 0) {
                echo "<script>alerta('error', 'Não pode haver dois TVGS pendentes ao mesmo tempo');</script>";
            } else {
                $sql = "INSERT INTO sessoes(nome, data_finalizacao, data_TVG, situacao) VALUES(:nome, NULL, :data, 'Pendente')";
    
                $consulta = $pdo->prepare($sql);
                $consulta->bindParam(':nome', $nome);
                $consulta->bindParam(':data', $data);
                if ($consulta->execute()) {

                    $query = "SELECT nome FROM sessoes WHERE id = ?";
                    $result = $pdo->prepare($query);
                    $result->bindValue(1, $id);
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

                    $querySessao = "SELECT data_TVG FROM sessoes ORDER BY data_criacao ASC LIMIT 1";
                    $resultado = $pdo->prepare($querySessao);
                    $resultado->execute();
                    $data = $resultado->fetchColumn();

                    $insert = "INSERT INTO log_sessoes (sessao, data_sessao, usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?,?,?, 'adição de sessão' , NOW() , NULL ,?)";
                    $stmt = $pdo->prepare($insert);
                    $stmt->bindValue(1, $name);
                    $stmt->bindValue(2, $data);
                    $stmt->bindValue(3, $user);
                    $stmt->bindValue(4, $ip_user);
                    $stmt->bindValue(5, $nome);
                    $stmt->execute();

                    header("location:gerenciamentoEdicao.php");
                    exit();
                } else {
                    echo "<script>alert('Favor inserir sessão!'); window.location.href='novaEdicao.php';</script>";
                }
            }
        }
    }
}