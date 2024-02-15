<?php
require_once "conexao.php";

if (isset($_POST["taskId"]) && isset($_POST["newStatus"])) {
    $taskId = $_POST["taskId"];
    $newStatus = $_POST["newStatus"];
   

    $atualizarSituacao = "UPDATE tarefas SET situacao = :situacao WHERE id = :id";
    $stmt = $pdo->prepare($atualizarSituacao);
    $stmt->bindParam(':situacao', $newStatus);
    $stmt->bindParam(':id', $taskId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'mensagem' => 'Falha ao atualizar a situação da tarefa']);
        exit();
    }
}
?>