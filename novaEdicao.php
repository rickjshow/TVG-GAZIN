<?php
include "header.php";
require_once "conexao.php";
require_once "permissao.php";
include "adicionarEdicao.php";
include "temporizador.php";

verificarPermissao($permission);

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





    <div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Edição TVG</h3>
            <button class="btn btn-primary mt-4" data-toggle="modal" style="font-size: 15px;" data-target="#exampleModal">Cadastrar Edição</button>
        </div>
    </div>

    <div class="container-fluid">
        <div class="table-responsive-sm mt-4" style="font-size: 12px;">
            <table class="table table-sm table-hover table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Data TVG</th>
                        <th>Situação</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM sessoes";
                    $consulta = $pdo->prepare($query);
                    $consulta->execute();
                    $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

                    $queryG = "SELECT s.id AS sessao_id FROM gerenciamento_sessao AS gs
                    JOIN sessoes AS s ON gs.id_sessoes = s.id";
                    $consulta2 = $pdo->prepare($queryG);
                    $consulta2->execute();
                    $dados = $consulta2->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($dados as $dado);
                    ?>

                    <?php
                       foreach ($data as $row) :
                        ?>
                        <tr>
                            <th><?php echo $row['nome']; ?></th>
                            <th><a><?php echo $row['data_TVG']; ?></a></th>
                            <td>
                                <a class="btn btn-<?php echo ($row['situacao'] == 'Pendente') ? 'danger' : 'success'; ?>" style="font-size: 12px;" id="btn">
                                    <?php echo $row['situacao']; ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                
                                $queryG = "SELECT s.id AS sessao_id FROM gerenciamento_sessao AS gs
                                            JOIN sessoes AS s ON gs.id_sessoes = s.id
                                            WHERE s.id = :sessao_id";
                                $consulta2 = $pdo->prepare($queryG);
                                $consulta2->bindValue(':sessao_id', $row['id']);
                                $consulta2->execute();
                                $equipesCadastradas = $consulta2->fetch(PDO::FETCH_ASSOC);
                    
                             
                                $paginaRedirecionar = ($equipesCadastradas) ? 'updateGSessao.php' : 'gerenciamentoEdicao.php';

                                    if($row['situacao'] != 'Finalizado')  : ?>
                                        <a href="<?php echo $paginaRedirecionar; ?>?id=<?php echo $row['id']; ?>" style="font-size: 12px;" class="btn btn-success">Atualizar</a>            
                                    <?php elseif($row['situacao'] != 'Pendente') : ?>
                                        <button class="btn btn-success" disabled style="font-size: 12px;">Atualizar</button>                           
                                    <?php  endif; ?>                                         
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <?php

            if($consultaVerifica->rowCount() < 1) {

                echo "    
                        <div class='container d-flex align-items-center justify-content-center' style='height: 30vh;'>
                            <p>Não existem Edições pendentes no momento</p>
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

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>