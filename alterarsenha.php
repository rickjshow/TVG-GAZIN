<?php
session_start();
require_once "conexao.php";

$user = $_GET['user'];

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(empty($_POST['pass']) || empty($_POST['confpass'])){
        echo "<script>alert('Favor inserir todos os dados!'); window.location.href='alterarsenha.php?user=$user';</script>";
    }else{
        if(isset($_POST['pass']) && isset($_POST['confpass']) && $_POST['pass'] !== $_POST['confpass']){
            echo "<script>alert('As senhas estão divergentes!'); window.location.href='alterarsenha.php?user=$user';</script>";
        }else{

        $senha = $_POST['confpass'];

        function validarSenha($senha) {

            $user = $_GET['user'];

            $comprimentoMinimo = 8;
            $temLetraMaiuscula = preg_match('/[A-Z]/', $senha);
            $temLetraMinuscula = preg_match('/[a-z]/', $senha);
            $temNumero = preg_match('/[0-9]/', $senha);
            $temCaracterEspecial = preg_match('/[^a-zA-Z0-9]/', $senha);

            if (strlen($senha) < $comprimentoMinimo) {
                echo "<script>alert('A senha deve conter pelo menos $comprimentoMinimo caracteres!'); window.location.href='alterarsenha.php?user=$user';</script>";
            } elseif (!$temLetraMaiuscula) {
                echo "<script>alert('A senha deve conter pelo menos uma letra maiuscula!'); window.location.href='alterarsenha.php?user=$user';</script>";
            } elseif(!$temLetraMinuscula){
                echo "<script>alert('A senha deve conter pelo menos uma letra minuscula!'); window.location.href='alterarsenha.php?user=$user';</script>";
            } elseif(!$temNumero){
                echo "<script>alert('A senha deve conter pelo menos um numero!'); window.location.href='alterarsenha.php?user=$user';</script>";
            } elseif(!$temCaracterEspecial){
                echo "<script>alert('A senha deve conter pelo menos um caracter especial!'); window.location.href='alterarsenha.php?user=$user';</script>";
            } else {
                return true;
            }
        }

        $validacao = validarSenha($senha);

        if($validacao === true){
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $update = "UPDATE usuarios SET senha = ?, senha_resetada = 'nao' WHERE nome = ?";
            $result = $pdo->prepare($update);
            $result->bindValue(1, $hash);
            $result->bindValue(2, $user);
            $result->execute();

            if($result){

                $user = $_GET['user'];

                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                } else {
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                }
                
                $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

                $insert = "INSERT INTO log_facilitadores (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?, 'Alteração da senha do usuário - $user' , NOW() , NULL ,NULL)";
                $stmt = $pdo->prepare($insert);
                $stmt->bindValue(1, $user);
                $stmt->bindValue(2, $ip_user);
                $stmt->execute();

                $querySessao = "INSERT INTO sessoes_usuarios_logados (usuario, data_login, ip_user) VALUES (?, NOW(), ?)";
                $result1 = $pdo->prepare($querySessao);
                $result1->bindValue(1, $user);
                $result1->bindValue(2, $ip_user);
                $result1->execute();

                $_SESSION["username"] = $user;
                session_start();
                $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Senha alterada com sucesso!');
                header("location: home.php");
                exit();
            }
            }else{
                echo "<script>alert('Erro na alteração da senha!');</script>";
            }
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
            Alterar senha
        </div>
        <form class="p-3 mt-3" method="POST">
            <div class="form-field d-flex align-items-center">
                <span class=" far fa-user"></span>
                <input type="password" name="pass" id="usuario" placeholder="Nova senha" autocomplete="off">
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="confpass" id="senha" placeholder="Confirme a senha" autocomplete="off">
            </div>
            <button class="btn mt-3">confirmar</button>
        </form>
    </div>
    <style>


    </style>
    <p class="mb-3 text-muted text-center">
            ©️ 2024 TVG. Todos os direitos reservados.
    </p>
</body>
</html>