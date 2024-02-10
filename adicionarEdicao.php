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
                    header("location:gerenciamentoEdicao.php");
                    exit();
                } else {
                    echo "<script>alert('Favor inserir sessão!'); window.location.href='novaEdicao.php';</script>";
                }
            }
        }
    }
}