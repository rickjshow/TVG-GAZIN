<?php
include "header.php";
require_once "conexao.php";
require_once "permissao.php";
include "adicionarEdicao.php";
include "temporizador.php";
require_once "tipoUser.php";

verificarPermissao($permission);

$resultado = verificarTipo($_SESSION['username']);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alert.js"></script>
    <title>Painel TVG</title>
</head>
<body>

<?php

$queryVerifica = "SELECT * FROM sessoes AS ses
JOIN gerenciamento_sessao AS gs ON ses.id = gs.id_sessoes
WHERE ses.situacao = 'Pendente'";
$consultaVerifica = $pdo->prepare($queryVerifica);
$consultaVerifica->execute();

?>


    <div class="container mt-4 mb-3">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Edição TVG</h3>
            <button class="btn btn-primary mt-4" data-toggle="modal" style="font-size: 15px;" data-target="#exampleModal">Cadastrar Edição</button>
            <?php 

                if($resultado == 'Desenvolvedor') : ?>
                    <a class="btn btn-success mt-4" href="logSessao.php">Log de Sessões</a>
                <?php endif;
            ?>
        </div>
    </div>

    <form id="formBusca">
        <div class="container mt-4">
            <div class="input-group mb-3">
                <input type="text" id="campoBusca" class="form-control" name="search" placeholder="Buscar TVG por nome" onkeyup="atualizarBusca()">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit" name="buscar" id="botaoBusca">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="container mt-2">
        <div class="container mt-sm-4 border rounded shadow">
        <div class="table-responsive-sm mt-4" style="font-size: 12px;">
            <table class="table table-sm table-hover table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Data</th>
                        <th>Situação</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <tbody id="tabelaResultados"> 
                </tbody>
            </table>
        </div>

        <?php

            if($consultaVerifica->rowCount() < 1) {

                echo "    
                        <div class='container d-flex align-items-center justify-content-center' style='height: 30vh;'>
                            <p>Sem TVG pendente no momento!</p>
                        </div>
                "; 

            }

        ?>

        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Adicionar Ediçao</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="adicionarEdicao.php" method="post">
                            <div class="form-group">
                                <label for="nometvg">Nome da Edição:</label>
                                <input type="text" name="nometvg" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="datatvg">Data do TVG:</label>
                                <input type="date" name="datatvg" class="form-control">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <input type="submit" class="btn btn-success" name="add_tvg" value="Adicionar">
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class='text-center mt-4'></div>
    <?php
            if (isset($_SESSION['alerta'])) {
            echo "<script>
                    alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                    </script>";
            unset($_SESSION['alerta']);
            }
    ?>

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

                                window.location.href = 'logout.php';
                            } else {

                                console.log('Usuário está ativo.');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
                setInterval(verificarSituacaoUsuario, 10000); 
            });

    </script>

<script>
    window.onload = function() {

        atualizarBusca('');
    };

    function atualizarBusca(busca) {
        $.ajax({
            url: 'buscaEdicao.php', 
            method: 'POST',
            data: { buscar: true, search: busca }, 
            success: function(response) {
                $('#tabelaResultados').html(response); 
            },
            error: function(xhr, status, error) {
                console.error(error); 
            }
        });
    }

    $(document).ready(function() {
        $('#campoBusca').keyup(function() {
            var busca = $(this).val();
            atualizarBusca(busca);
        });
    });
</script>



    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>