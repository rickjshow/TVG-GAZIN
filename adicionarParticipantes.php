<?php

require_once "conexao.php";

if (isset($_POST["add_participantes"])) {
    if (empty($_POST["nome"]) || empty($_POST["departamentos"])) {
        $_SESSION['alerta'] = 'Favor inserir todos os dados!';
        header("location: participantes.php");
        exit();
    } else {
        $nome = $_POST["nome"];
        $departamento_nome = $_POST["departamentos"];

        $queryUsername = "SELECT nome FROM participantes WHERE nome = :nome";
        $consultanome = $pdo->prepare($queryUsername);
        $consultanome->bindParam(':nome', $nome);
        $consultanome->execute();

        if ($consultanome->rowCount() > 0) {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Nome do participante ja existente!');
            header("location: participantes.php");
            exit();
        } else {
            $sqlDepartamento = "SELECT id FROM departamentos WHERE name = :departamento_nome";
            $consultaDepartamento = $pdo->prepare($sqlDepartamento);
            $consultaDepartamento->bindParam(':departamento_nome', $departamento_nome);
            $consultaDepartamento->execute();

            if ($consultaDepartamento->rowCount() == 0) {
                $_SESSION['alerta'] = 'Erro: Departamento inválido.';
                header("location: participantes.php");
                exit();
            } else {
                $departamento_id = $consultaDepartamento->fetchColumn();

                $sql = "INSERT INTO participantes(nome, id_departamentos) VALUES(:nome, :id_departamentos)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindParam(':nome', $nome);
                $consulta->bindParam(':id_departamentos', $departamento_id);

                if ($consulta->execute()) {

                    $user = $_SESSION['username'];

                    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                    } else {
                        $ip_address = $_SERVER['REMOTE_ADDR'];
                    }
                    
                    $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

                    $insert = "INSERT INTO log_participantes (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?, 'adição de participante' , NOW() , NULL ,?)";
                    $stmt = $pdo->prepare($insert);
                    $stmt->bindValue(1, $user);
                    $stmt->bindValue(2, $ip_user);
                    $stmt->bindValue(3, $nome);
                    $stmt->execute();

                    $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Cadastrado com sucesso!');
                    header("location: participantes.php");
                    exit();
                } else {
                    session_start();
                    $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Falha ao cadastrar usuário');
                    header("location: participantes.php");
                    exit();
                }
            }
        }
    }
}
?>