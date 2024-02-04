<?php

include "conexao.php";

if (isset($_POST['enviar'])) {
    if (isset($_FILES['imgPerfil']) && $_FILES['imgPerfil']['error'] === UPLOAD_ERR_OK) {

        $user = $_SESSION['username'];
        $queryId = "SELECT id FROM usuarios WHERE nome = :user";
        $consultaId = $pdo->prepare($queryId);
        $consultaId->bindParam(':user', $user);
        $consultaId->execute();
        $Id = $consultaId->fetch(PDO::FETCH_ASSOC);

        $imagem_tmp = $_FILES['imgPerfil']['tmp_name'];

        $conteudo_imagem = fopen($imagem_tmp, 'rb');

        $query = "UPDATE usuarios SET fotos = :imagem WHERE id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':imagem', $conteudo_imagem, PDO::PARAM_LOB);
        $stmt->bindParam(':id', $Id['id'], PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $timestamp = time();
                header("location: home.php");
                exit();
        } else {
            echo "Falha ao atualizar a imagem.";
        }

        fclose($conteudo_imagem);
    } else {
        echo "Erro no envio do arquivo.";
    }
}

?>