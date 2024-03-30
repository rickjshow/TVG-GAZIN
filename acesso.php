<?php
require_once "conexao.php";
require_once "permissao.php";
include "header.php";
include "adicionar.php";
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
   
    <div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
        <?php 
            if($resultado == 'Desenvolvedor') : ?>
                <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Cadastro de Usuarios</h3>
            <?php else : ?>
                <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Cadastro de Facilitadores</h3>
            <?php endif; ?>
            <button class="btn btn-primary mt-4" data-toggle="modal" style="font-size: 15px;" data-target="#exampleModal">Cadastrar Usuarios</button>
            <?php 

                if($resultado == 'Desenvolvedor') : ?>
                    <a class="btn btn-success mt-4" href="logFacilitadores.php">Log de Usuarios</a>
                <?php endif;
            ?>
        </div>
    </div>

    <form id="formBusca">
        <div class="container mt-4">
            <div class="input-group mb-3">
                <input type="text" id="campoBusca" class="form-control" name="search" placeholder="Buscar usuário por nome" onkeyup="atualizarBusca()">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit" name="buscar" id="botaoBusca">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>

    
    <div class="container mt-4">
        <div class="container mt-sm-4 border rounded shadow">
        <div class="table-responsive-sm " style="font-size: 12px;">
            <table class="table table-sm table-hover table-striped">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Setor</th>
                        <th>Editar</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                     <tbody id="tabelaResultados"> 
                </tbody>
            </table>
        </div>
        

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Adicionar Usuário</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="acesso.php" method="post">
                    <div class="form-group">
                        <label for="usuario">Usuário:</label>
                        <input type="text" name="nome" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="departamentos">Departamento:</label>
                        <select name="departamentos" class="form-control">
                            <?php
                            $query = "SELECT * FROM departamentos ORDER BY name";
                            $consulta = $pdo->prepare($query);
                            $consulta->execute();
                            $departamentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($departamentos as $row) : ?>
                                <option value="<?= $row['name'] ?>" <?= $row['name'] == "E-commerce" ? "selected" : "" ?>>
                                    <?= $row['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo">Tipo de usuário:</label>
                        <select name="tipo" class="form-control">
                            <?php

                            if($resultado == "Desenvolvedor"){
                                $query = "SELECT * FROM tipo ORDER BY id DESC";
                            }else{
                                $query = "SELECT * FROM tipo WHERE tipo NOT IN('DESENVOLVEDOR') ORDER BY id DESC";
                            }
                            
                            $consulta = $pdo->prepare($query);
                            $consulta->execute();
                            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($data as $row) : ?>
                                <option value="<?= $row['tipo'] ?>" <?= $row['tipo'] == "FACILITADORES" ? "selected" : "" ?>>
                                    <?= $row['tipo'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <input type="submit" class="btn btn-success" name="add_user" value="Adicionar">
            </div>
            </form>
            
            <?php
                    if (isset($_SESSION['alerta'])) {
                    echo "<script>
                            alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                            </script>";
                    unset($_SESSION['alerta']);
                    }
            ?>
        </div>
    </div>
</div>
</div>
<div class='text-center mt-4'></div>

    <div id="login-expired-message" style="color: black;"></div>
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


<script>
    window.onload = function() {

        atualizarBusca('');
    };

    function atualizarBusca(busca) {
        $.ajax({
            url: 'buscaUsuarios.php', 
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


<script>
    resetTimer();
</script>



<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>