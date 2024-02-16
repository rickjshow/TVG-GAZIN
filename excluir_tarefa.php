<?php

include "conexao.php";

if (isset($_POST["Id"])) {
    $id = $_POST['Id'];
} else {
    echo json_encode(['success' => false, 'mensagem' => 'ID da tarefa não fornecido']);
    exit();
}

$excluirTarefa = "DELETE FROM tarefas WHERE id = :id";
$resultadoDaExclusao = $pdo->prepare($excluirTarefa);
$resultadoDaExclusao->bindValue(":id", $id, PDO::PARAM_INT);
$resultadoDaExclusao->execute();

if ($resultadoDaExclusao) {
    echo json_encode(['success' => true]);
    exit();
} else {
    echo json_encode(['success' => false, 'mensagem' => 'Falha ao excluir a tarefa']);
    exit();
}

?>