<?php

require_once("conexao.php");
require_once "permissao.php";
include "temporizador.php";
require_once "header.php";

verificarPermissao($permission);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sqlProvas = "
        SELECT * FROM provas WHERE id = :id 
    ";

    $consulta = $pdo->prepare($sqlProvas);
    $consulta->bindParam(':id', $id, PDO::PARAM_INT);
    $consulta->execute();

    if (!$consulta) {
        die("Consulta falha");
    }

    $row = $consulta->fetch(PDO::FETCH_ASSOC);


    if (!$row) {
        die("Não foi possível recuperar os dados do banco de dados:<br> 
         Erro login: " . print_r($consulta->errorInfo(), true));
    }
}

if (isset($_POST['update_prova'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $pergunta = $_POST['pergunta'];
    $pontos = $_POST['pontos'];

    $sqlprova = "
    UPDATE provas
    SET
        nome = :nome,
        descricao = :descricao,
        pergunta = :pergunta,
        pontuacao_maxima = :pontos
    WHERE id = :id 
    ";

    $consulta = $pdo->prepare($sqlprova);
    $consulta->bindValue(':nome', $nome);
    $consulta->bindValue(':descricao', $descricao);
    $consulta->bindValue(':pergunta', $pergunta);
    $consulta->bindValue(':pontos', $pontos);
    $consulta->bindValue(':id', $id, PDO::PARAM_INT);

    if ($consulta->execute()) {
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Atualizado sucesso!');
        header("location: cadastro_provas.php");
        exit();
    } else {
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Falha ao atualizar prova');
        header("location: cadastro_provas.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> 
    <title>UpdateProva</title>
</head>
<body>


    <div class="text-center mt-4"></div>
    
<div class="container">
    <div class="container mt-4 border rounded p-4 shadow">
        <div class="mx-auto col-md-6">
            <form action="updateProva.php" method="post">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <h2 class="font-weight-bold text-center">Atualizar Vivência</h2>
                <div class="form-group">
                    <label for="nome" class="mt-4">Nome da Vivência:</label>
                    <input type="text" name="nome" class="form-control" value="<?= $row['nome'] ?>">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea class="form-control" name="descricao" rows="5"><?= $row['descricao'] ?></textarea>
                </div>

                <div class="form-group">
                    <label for="pergunta">Perguntas:</label>
                    <textarea class="form-control" name="pergunta" rows="5"><?= $row['pergunta'] ?></textarea>
                </div>

                <div class="form-group">
                    <label for="pontos">Pontuação Máxima da Prova:</label>
                    <input type="number" name="pontos" class="form-control" value="<?= $row['pontuacao_maxima'] ?>">
                </div>

                <input type="submit" style="font-size: 15px" class="btn btn-success" name="update_prova" value="ATUALIZAR">
            </form>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>

