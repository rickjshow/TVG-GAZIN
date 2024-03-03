<?php
include "header.php";
include "conexao.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <title>dashboard</title>
</head>

<body>

    <div class="container mt-4 mb-3">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Dashboard</h3>
        </div>
    </div>

<div class="container">
    <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-green f-w-600 p-2">38</h4>
                                <h6 class="text-muted m-b-0 p-2">Participantes Presente</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-green">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <p class="text-white m-b-0"></p>
                            </div>
                            <div class="col-3 text-right">
                            <i class="fa fa-area-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-red f-w-600 p-2">6</h4>
                                <h6 class="text-muted m-b-0 p-2">Participantes Ausentes</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-red">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <p class="text-white m-b-0"></p>
                            </div>
                            <div class="col-3 text-right">
                            <i class="fa fa-area-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php 
                $queryFacilitadores = "SELECT COUNT(*) FROM  tipo AS tipo
                JOIN usuarios AS u ON tipo.id = u.id_tipo
                WHERE u.situacao  = 'Ativo' AND u.permission = 'limited'";
                $stmtFacilitadores = $pdo->prepare($queryFacilitadores);
                $stmtFacilitadores->execute();
                $Facilitadores = $stmtFacilitadores->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-orange f-w-600 p-2"><?php echo $Facilitadores[0]['COUNT(*)']; ?></h4>
                                <h6 class="text-muted m-b-0 p-2">Quantidade de Facilitadores</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-orange">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <p class="text-white m-b-0"></p>
                            </div>
                            <div class="col-3 text-right">
                            <i class="fa fa-area-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>



        
        <?php 
       
            $querySessao = "SELECT id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
            $stmtSessao = $pdo->prepare($querySessao);
            $stmtSessao->execute();
            $idSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);

            if ($idSessao) {
                echo $idSessao['id'];
            }
        
        ?>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-blue f-w-600 p-2">3987</h4>
                                <h6 class="text-muted m-b-0 p-2">Maior Pontuaçãoo</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-blue">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <p class="text-white m-b-0">Equipe: Amarela</p>
                            </div>
                            <div class="col-3 text-right">
                            <i class="fa fa-area-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</body>
</html>