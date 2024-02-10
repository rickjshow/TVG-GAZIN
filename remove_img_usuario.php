<?php

include "conexao.php";

if (isset($_POST['remove_img'])) {
    $caminhoImagemPadrao = 'semfoto.jpg';

    $conteudoImagemPadrao = file_get_contents($caminhoImagemPadrao);

    $user = $_SESSION['username'];
    
    $queryId = "SELECT id FROM usuarios WHERE nome = :user";
    $consultaId = $pdo->prepare($queryId);
    $consultaId->bindParam(':user', $user);
    $consultaId->execute();
    $Id = $consultaId->fetch(PDO::FETCH_ASSOC);

    
    $query = "UPDATE usuarios SET fotos = :imagem WHERE id = :id";
    $stmt = $pdo->prepare($query);

   
    $stmt->bindParam(':imagem', $conteudoImagemPadrao, PDO::PARAM_LOB);

    $stmt->bindParam(':id', $Id['id'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
    
        $timestamp = time();
        header("location: home.php");
        exit();
    } else {
        echo "Falha ao atualizar a imagem.";
    }
}
?>