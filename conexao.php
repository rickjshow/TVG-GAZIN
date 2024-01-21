<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "tvg";

try {
    $pdo = new PDO("mysql:host={$servidor};dbname={$banco};port=3306;charset=utf8;", $usuario, $senha);


    if (!$pdo) {
        echo "Erro ao se conectar no banco";
        exit;
    }
} catch (\Exception $e) {
    echo "Erro ao se conectar no banco";
    echo $e->getMessage();
    exit;
}
