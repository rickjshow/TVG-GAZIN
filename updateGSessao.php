<?php

require_once ("conexao.php");
include "header.php";
require_once "permissao.php";
include "temporizador.php";

verificarPermissao($permission);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>Edição gerenciamento sessão</title>
</head>

<body>
    
        <div class="box1 mt-4 text-center">
            <h1 class="mt-4 text-center" style='font-size: 25px;'>Equipes</h1>
        </div>

    <div class="container-fluid text-center">
        <?php

        if (isset($_GET['id'])) {
            $idGS = $_GET['id'];
        } else {
            echo "<script>alert('ID não encontrado!');</script>";
        }

        $queryGsession = "SELECT e.nome AS equipe_nome, e.id AS id_equipe FROM gerenciamento_sessao AS gs
                    JOIN equipes AS e ON gs.id_equipe = e.id
                    WHERE gs.id_sessoes = :idGS 
                    GROUP BY gs.id_equipe";
        $consultaid = $pdo->prepare($queryGsession);
        $consultaid->bindParam(':idGS', $idGS);
        $consultaid->execute();

        $dadosequipe = $consultaid->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dadosequipe as $row) {
         
            $equipe_nome = htmlspecialchars($row['equipe_nome']);
            $idGS = htmlspecialchars($idGS);

            echo "<div class='row'>
                        <div class='col-sm-6 mb-3 mb-sm-0 mx-auto'>
                            <a href='updateEquipeSessao.php?id={$row['id_equipe']}' style='text-decoration: none; color: black;'>
                                <div class='card mt-5 p-3 shadow-sm rounded' style='background-color: #f8f9fa;'>
                                    <div class='card-body text-center'>
                                        <h4 class='mb-3' style='color: #007bff;'>{$equipe_nome}</h4>
                                        <p class='font-weight-bold'>ID da Sessão: {$idGS}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>";
        }


        ?>
    </div>
    
        <div class="container-fluid text-center mt-1 p-4">
            <button id="btnExcluirSessao" class="btn btn-danger" disabled style="font-size: 12px;">Excluir Sessão</button>
        </div>



        <div class="container-fluid text-center mt-1 p-4">
            <?php 
                if (isset($_GET['id'])) {
                    $idGS = $_GET['id'];
                } else {
                    echo "<script>alert('ID não encontrado!');</script>";
                }

                $query = "SELECT COUNT(*) AS total_provas_nao_finalizadas
                        FROM equipes_provas
                        WHERE id_sessao = :id_sessao
                        AND situacao <> 'Finalizado'";

                $consulta = $pdo->prepare($query);
                $consulta->bindValue(':id_sessao', $idGS);  
                $consulta->execute();
                $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

                $totalProvasNaoFinalizadas = $resultado['total_provas_nao_finalizadas'];
            ?>

            <div class="container-fluid text-center mt-1 p-4">
                <?php
                    if ($totalProvasNaoFinalizadas > 0) {
                        echo '<p>Ainda há provas não finalizadas. Não é possível encerrar a sessão.</p>';
                    } elseif($totalProvasNaoFinalizadas <= 0) {
                    echo "<form action='finalizarSessao.php' method='post'>
                            <a href='finalizarSessao.php?id={$idGS}' type='submit' class='btn btn-secondary' data-dismiss='modal'>Finalizar Sessão</a>
                        </form>";
                    }
                ?>
            </div>
        </div>
        
 <script>


    $(document).ready(function() {
        $("#btnExcluirSessao").prop("disabled", false);

        $("#btnExcluirSessao").click(function() {
            var idSessao = "<?php echo $idGS; ?>";

            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Esta ação encerrará a sessão. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, encerrar sessão!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'excluiSessao.php',
                        data: { idSessao: idSessao },
                        success: function(response) {
                            // Redirecionar para excluiSessao.php com o ID na URL
                            window.location.href = 'excluiSessao.php?idGS=' + idSessao;
                        },
                        error: function(error) {
                            console.error('Erro ao excluir sessão:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao encerrar a sessão. Por favor, tente novamente.'
                            });
                        }
                    });
                }
            });
        });
    });


</script>
    

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>