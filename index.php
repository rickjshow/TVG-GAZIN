<?php
session_start();
require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty($_POST['nome']) || empty($_POST['senha'])){
        echo "<script>alert('Por favor, preencha todos os campos!');</script>";
    }else{
        $username = $_POST["nome"];
        $password = $_POST["senha"];
    
        $query = "SELECT * FROM usuarios WHERE nome = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":username", $username);
        
        try {
            $stmt->execute();
            $user = $stmt->fetch();
            if(isset($user['senha']) && isset($user['senha'])){
                $senha = $user['senha'];
                $nome = $user['nome'];
            }
        
            if($user && password_verify($password, $senha)) {

                if ($user['situacao'] == 'Ativo') {

                    if($user['senha_resetada'] == 'sim'){
                        header("location: alterarsenha.php?user=$nome");
                        exit();
                    }

                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                } else {
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                }
                
                $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

                $querySessao = "INSERT INTO sessoes_usuarios_logados (usuario, data_login, ip_user) VALUES (?, NOW(), ?)";
                $result1 = $pdo->prepare($querySessao);
                $result1->bindValue(1, $user['nome']);
                $result1->bindValue(2, $ip_user);
                $result1->execute();    

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="icon" href="iconegazin.png" type="image/x-icon">
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
    <style>


    </style>
    <p class="mb-3 text-muted text-center">
            ©️ 2024 TVG. Todos os direitos reservados.
    </p>
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