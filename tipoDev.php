<?php

require_once "conexao.php";

$type = 'Desenvolvedor';

function verificarTipo($type)
{
    if (!isset($_SESSION['username'])) {
        header("location:index.php");
        exit();
    }

    $username = $_SESSION['username'];
    $tipo = obterTipoDoBancoDeDados($username);

    if ($tipo !== $type) {
        header("location:home.php");
        exit();
    }
}

function obterTipoDoBancoDeDados($username){
    global $pdo;

    $queryPerm = "SELECT t.tipo AS tipo FROM tipo AS t
    JOIN usuarios AS u ON t.id = u.id_tipo
    WHERE u.nome = :username";
    $stmt = $pdo->prepare($queryPerm);
    $stmt->bindParam(":username", $username);
    
    $stmt->execute();

    $stmt->bindColumn("tipo", $tipo);
    $stmt->fetch(PDO::FETCH_BOUND);
    $stmt->closeCursor();

    return $tipo;
}