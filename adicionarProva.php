<?php
require_once "conexao.php";
if (isset($_POST["add_prova"])) {
    if (empty($_POST["nome_prova"]) || empty($_POST["descricao_prova"]) || empty($_POST['pergunta_prova']) || empty($_POST['pontos'])) {
        echo "<script>alert('Favor inserir todos os dados!'); window.location.href='cadastro_provas.php';</script>";
        exit();
    } else {
        $nome = $_POST["nome_prova"];
        $descricao = $_POST["descricao_prova"];
        $pergunta = $_POST["pergunta_prova"];
        $pontos = $_POST['pontos'];

        $sql = "INSERT INTO provas(nome, descricao, pergunta, pontuacao_maxima) VALUES(:nome, :descricao, :pergunta, :pontos)";

        $consulta = $pdo->prepare($sql);
        $consulta->bindParam(':nome', $nome);
        $consulta->bindParam(':descricao', $descricao);
        $consulta->bindParam(':pergunta', $pergunta);
        $consulta->bindParam(':pontos', $pontos);
        if ($consulta->execute()) {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Cadastrado com sucesso!');
            header("location: cadastro_provas.php");
            exit();
        } else {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Falha ao cadastrar prova');
            header("location: cadastro_provas.php");
            exit();
        }
    }
}
