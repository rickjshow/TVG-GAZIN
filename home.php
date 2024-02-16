<?php

include "header.php";
include "temporizador.php";
require_once "conexao.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alertSucess.js"></script>
    <script src="alert.js"></script>
    <title>Painel TVG</title>
    <style>
        @media (max-width: 768px) {
            .card {
                margin: 0 10px;
            }
        }
    
    </style>
</head>

<body>    

        <?php 

            if (isset($_SESSION['alerta'])) {
            echo "<script>
                    alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                    </script>";
            unset($_SESSION['alerta']);
            }

        ?>

        <div class='dashboard-content mt-5'>
            <div class='container'>
                <div class='card mx-auto d-flex text-center'> 
                    <div class='card-header'>

                        <?php if (isset($_SESSION['username'])) : ?>
                            <span class="nav-link" style="vertical-align: top; font-size: 14px;">
                                <strong style="color: black;">Olá, <?php echo $_SESSION['username']; ?> seja bem-vindo(a)!</strong>
                            </span>
                        <?php endif; ?>

                    </div>

                    <div class='card-body'>
                        <?php
                        $querySessao = "SELECT u.id_tipo, t.tipo FROM usuarios AS u
                                            JOIN tipo AS t ON u.id_tipo = t.id
                                            WHERE u.nome = :username";

                        $stmttipo = $pdo->prepare($querySessao);
                        $stmttipo->bindParam(':username', $username, PDO::PARAM_STR);
                        $stmttipo->execute();
                        $dadosUsuario = $stmttipo->fetch(PDO::FETCH_ASSOC);

                        if ($dadosUsuario) {
                            $nomeTipo = $dadosUsuario['tipo'];
                            echo "<p class='mt-1'> Tipo de usuario: $nomeTipo</p>";
                        } else {
                            echo "<p class='mt-1'> Tipo de usuario não encontrado.</p>";
                        }
                        ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div id="login-expired-message" style="color: black;"></div>
    <script>
        resetTimer();
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
                function verificarSituacaoUsuario() {
                    $.ajax({
                        url: 'verificarUser.php',
                        method: 'POST',
                        success: function(response) {
                            var data = JSON.parse(response);
                            if (data.status === 'inativo') {
                                // Redirecionar para a página de logout ou mostrar uma mensagem
                                window.location.href = 'logout.php';
                            } else {
                                // Usuário ativo, pode continuar normalmente
                                console.log('Usuário está ativo.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
                setInterval(verificarSituacaoUsuario, 10000); // Verificar a cada 10 segundos
            });
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</body>
</html>