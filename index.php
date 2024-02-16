<?php
session_start();
require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($_POST['nome']) || empty($_POST['senha'])){
        echo "<script>alert('Por favor, preencha todos os campos!');</script>";
    }else{
        $username = $_POST["nome"];
        $password = $_POST["senha"];
    
        $query = "SELECT * FROM usuarios WHERE nome = :username AND senha = :password";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);
    
        try {
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user) {
                if ($user['situacao'] == 'Ativo') {

                    $_SESSION["username"] = $username;
                    header("location: home.php");

                } elseif ($user['situacao'] == 'Inativo') {
                    echo "<script>alert('Usuário Inativo Temporariamente!');</script>";
                }
            } else {
                echo "<script>alert('Usuário ou senha incorretos. Tente novamente.');</script>";
            }
        } catch (PDOException $e) {
            echo "Erro: " . $e->getMessage();
            var_dump($e);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alert.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" 
    rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <title>TVG GAZIN</title>
</head>

<body>

    <div class="wrapper">
        <div class="logo">
            <img src="jeitogazinlogo.png" alt="jeitogazinlogo">
        </div>
        <div class="text-center mt-4 name">
            TVG
        </div>
        <form class="p-3 mt-3" method="POST">
            <div class="form-field d-flex align-items-center">
                <span class=" far fa-user"></span>
                <input type="text" name="nome" id="usuario" placeholder="Usuário" autocomplete="off">
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="senha" id="senha" placeholder="Senha" autocomplete="off">
            </div>
            <button class="btn mt-3">Login</button>
        </form>
    </div>
    <?php 
    
        if (isset($_SESSION['alerta'])) {
            echo "<script>
                    alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                    </script>";
            unset($_SESSION['alerta']);
            session_destroy();
        }
    
    ?>
</body>
</html>