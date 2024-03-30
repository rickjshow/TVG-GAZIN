<?php 

    include "conexao.php";
    require_once "permissao.php";
    include "temporizador.php";
    include "header.php";
    
    verificarPermissao($permission);

    $querySessao = "SELECT nome, id FROM sessoes ORDER BY data_criacao DESC LIMIT 1";
    $stmtSessao = $pdo->prepare($querySessao);
    $stmtSessao->execute();
    $nomeSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);

    if(isset($nomeSessao['nome'])){
        $nomeSession = $nomeSessao['nome'];
    }else{
        $nomeSession = "Não existem sessões cadastradas!";
    }
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .box1 {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        table {
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        th,
        td {
            text-align: center;
            font-size: 16px;
            padding: 15px;
        }

        th {
            background-color: #343a40;
            color: white;
            position: relative;
            font-size: 18px;
        }

        th i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
        }
        
    </style>
</head>
<body>

    <div class='container mt-4'>
        <div class='mt-4 text-center p-4 border rounded shadow text-primary ">
            <h1 class="mt-4 font-weight-bold' style='font-size: px;'>Ranking Tvg Gazin</h1>
            <h4 class='mt-4 text-center mx-auto' style=' color: black; max-width: 600px; font-size: 0.9em; padding:5px; border:solid #000 1px;'>Ranking referente a sessão: <?php echo $nomeSession ?> </h4>
        </div>

    <div class="container mt-4">
        <div class="container-fluid">
            <table id="ranking-table" class="table table-striped mt-4" >
                <thead>
                    <tr>
                        <th scope="col"><i class="fa-solid fa-trophy"></i></th>
                        <th scope="col">Equipe</th>
                        <th scope="col">Pontuação </i></th>
                        <th scope="col">Lider</th>
                    </tr>
                </thead>
                <tbody id="ranking-body">
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            function atualizarRanking() {
                $.ajax({
                    url: 'atualizar_ranking.php',
                    type: 'GET',
                    success: function(data) {
                        $('#ranking-body').html(data);
                    },
                    error: function() {
                        console.log('Erro ao atualizar o ranking.');
                    }
                });
            }

            setInterval(atualizarRanking, 2000);
        });
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
</body>
</html>