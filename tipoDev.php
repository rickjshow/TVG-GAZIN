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

function obterTipoDoBancoDeDados($username)
{
    global $pdo;

    $queryPerm = "SELECT permission FROM usuarios WHERE nome = :username";
    $stmt = $pdo->prepare($queryPerm);
    $stmt->bindParam(":username", $username);
    
    $stmt->execute();

    $stmt->bindColumn("permission", $tipo);
    $stmt->fetch(PDO::FETCH_BOUND);
    $stmt->closeCursor();

    return $tipo;
}