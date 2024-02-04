<?php 

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "tvg";

iniciarSessaoSeNecessario();

try {
    $pdo = new PDO("mysql:host={$servidor};dbname={$banco};port=3306;charset=utf8;", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\Exception $e) {
    echo "Erro ao se conectar no banco";
    echo $e->getMessage();
    exit;
}