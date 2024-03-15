<?php

    function verificarTipo($user){

        include "conexao.php";

        $queryPermission = "SELECT t.tipo AS tipo FROM usuarios AS u
        JOIN tipo AS t ON u.id_tipo = t.id
        WHERE nome = ?";
        $result = $pdo->prepare($queryPermission);
        $result->bindValue(1, $user);
        $result->execute();
        $tipo = $result->fetchColumn();

        return $tipo;
    }

?>