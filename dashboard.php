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
                                <h4 class="text-c-green f-w-600 p-2">fazer</h4>
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
                                <h4 class="text-c-red f-w-600 p-2">fazer</h4>
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
            try {
                $querySessao = "SELECT e.nome AS nome_equipe, SUM(p.ponto_obtido) AS total_pontos
                                    FROM sessoes AS s
                                    JOIN pontuacao AS p ON s.id = p.id_sessoes
                                    JOIN equipes AS e ON e.id = p.id_equipes
                                    WHERE s.situacao = 'Pendente'
                                    GROUP BY e.nome
                                    ORDER BY total_pontos DESC, MIN(p.tempo_gasto) ASC
                                    LIMIT 1";

                $stmtSessao = $pdo->prepare($querySessao);
                $stmtSessao->execute();

                if ($stmtSessao->rowCount() > 0) {
                    $equipeMaisPontos = $stmtSessao->fetch(PDO::FETCH_ASSOC);
                } else {
                    $equipeMaisPontos = false;
                }
            } catch (PDOException $e) {
                echo "Erro na execução da consulta: " . $e->getMessage();
            }

            ?>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-blue f-w-600 p-2"><?php echo isset($equipeMaisPontos['total_pontos']) ? number_format($equipeMaisPontos['total_pontos'], 0, ',', '.') : '0'; ?></h4>
                                <h6 class="text-muted m-b-0 p-2">Maior Pontuação</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-blue">
                        <div class="row align-items-center">
                            <div class="col-9">
                                <p class="text-white m-b-0">Equipe: <?php echo isset($equipeMaisPontos['nome_equipe']) ? $equipeMaisPontos['nome_equipe'] : ''; ?></p>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-area-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php 
            $queryTempoTotal = "SELECT SUM(p.tempo_gasto) AS tempo_total
                                FROM pontuacao AS p
                                JOIN sessoes AS s ON p.id_sessoes = s.id
                                WHERE s.situacao = 'Pendente'";
            $stmtTempo = $pdo->prepare($queryTempoTotal);
            $stmtTempo->execute();
            $tempoTotal = $stmtTempo->fetch(PDO::FETCH_ASSOC);


            $segundosTotais = $tempoTotal['tempo_total'];
           
            $horas = floor($segundosTotais / 3600);
            $minutos = floor(($segundosTotais / 60) % 60);
            $segundos = $segundosTotais % 60;
            $tempoFormatado = sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);
        ?>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                               <h4 class="text-c-purple f-w-600 p-2"><?php echo $tempoFormatado; ?></h4>
                                <h6 class="text-muted m-b-0 p-2">Tempo Provas Total</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-purple">
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
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</body>

</html>