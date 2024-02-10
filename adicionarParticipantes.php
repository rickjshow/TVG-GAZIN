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
                    session_start();
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