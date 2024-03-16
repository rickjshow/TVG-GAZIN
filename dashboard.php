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

    <?php

    $nomeSelecionado = '';
    $idSessaoSelecionada = null;


    if (isset($_POST['filtrar'])) {

        $nomeSelecionado = $_POST['tvg'];


        $querySessao = "SELECT id FROM sessoes WHERE nome = :nomeSelecionado";
        $stmtSessao = $pdo->prepare($querySessao);
        $stmtSessao->bindParam(':nomeSelecionado', $nomeSelecionado, PDO::PARAM_STR);
        $stmtSessao->execute();

        // Verifica se a sessão foi encontrada
        if ($row = $stmtSessao->fetch(PDO::FETCH_ASSOC)) {
            $idSessaoSelecionada = $row['id'];
        }
    }

    echo "<form method='post' action='dashboard.php' class='mt-4'>
        <div class='form-row align-items-center justify-content-center'>
            <div class='col-auto'>
                <label class='sr-only' for='tvg'>Selecione o TVG:</label>
                <select class='custom-select mr-sm-2' name='tvg' id='tvg'>";
    $query = "SELECT * FROM sessoes ORDER BY data_criacao DESC";
    $querySessao = $pdo->prepare($query);
    $querySessao->execute();
    $SessaoName = $querySessao->fetchAll(PDO::FETCH_ASSOC);
    $nomeSelecionado = isset($_POST['tvg']) ? $_POST['tvg'] : '';
    echo "<option value='' " . ($nomeSelecionado === '' ? 'selected' : '') . ">Selecionar</option>";
    foreach ($SessaoName as $row) {

        $selected = ($row['nome'] == $nomeSelecionado) ? 'selected' : '';
        echo "<option value='" . $row['nome'] . "' $selected>" . $row['nome'] . "</option>";
    }
    echo "</select>
                </div>
                <div class='col-auto'>
                    <button type='submit' name='filtrar' class='btn btn-primary'>Filtrar</button>
                </div>
            </div>
        </form>";


    ?>

    <div class="container mt-4">
        <div class="row">


            <?php
            $queryPartTotal = "SELECT COUNT(*) AS total_participantes
               FROM gerenciamento_sessao AS gs
               JOIN sessoes AS s ON gs.id_sessoes = s.id
               JOIN participantes AS p ON gs.id_participantes = p.id
               WHERE gs.id_sessoes = :idSessaoSelecionada ";

            $consulta = $pdo->prepare($queryPartTotal);
            $consulta->bindParam(':idSessaoSelecionada', $idSessaoSelecionada, PDO::PARAM_INT);

            try {
                $consulta->execute();
                $resultTotal = $consulta->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }

            ?>


            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-blus f-w-600 p-2"><?php echo $resultTotal['total_participantes'] ?></h4>
                                <h6 class="text-muted m-b-0 p-2">Quantidade Participantes Total</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-blus">
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
            $query = "SELECT COUNT(DISTINCT p.id) AS total_participantes_presentes
               FROM presenca AS pre
               JOIN sessoes AS s ON pre.id_sessao = s.id
               JOIN gerenciamento_sessao AS gs ON s.id = gs.id_sessoes
               JOIN status AS statos ON pre.id_status = statos.id
               JOIN participantes AS p ON pre.id_participantes = p.id
               WHERE gs.id_sessoes = :idSessaoSelecionada AND statos.nome = 'Presente'";

            $consulta = $pdo->prepare($query);
            $consulta->bindParam(':idSessaoSelecionada', $idSessaoSelecionada, PDO::PARAM_INT);

            try {
                $consulta->execute();
                $result = $consulta->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }

            ?>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-green f-w-600 p-2"><?php echo  $result['total_participantes_presentes'] ?></h4>
                                <h6 class="text-muted m-b-0 p-2">Participantes Presentes</h6>
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



            <?php
                 $query = "SELECT COUNT(DISTINCT p.id) AS total_participantes_ausentes
                FROM presenca AS pre
                JOIN sessoes AS s ON pre.id_sessao = s.id
                JOIN gerenciamento_sessao AS gs ON s.id = gs.id_sessoes
                JOIN status AS statos ON pre.id_status = statos.id
                JOIN participantes AS p ON pre.id_participantes = p.id
                WHERE gs.id_sessoes = :idSessaoSelecionada AND statos.nome = 'Ausente'";

                $consulta = $pdo->prepare($query);
                $consulta->bindParam(':idSessaoSelecionada', $idSessaoSelecionada, PDO::PARAM_INT);

            try {
                $consulta->execute();
                $resultAusentes = $consulta->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }

            ?>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-red f-w-600 p-2"><?php echo  $resultAusentes['total_participantes_ausentes'] ?></h4>
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

            $totalParticipantesPresentes =  $result['total_participantes_presentes'] ?: 0;
            $totalParticipantes = $resultTotal['total_participantes'] ?: 0;

            $porcentagemParticipacao = ($totalParticipantes !== 0) ? number_format(($totalParticipantesPresentes / $totalParticipantes) * 100, 2) : 0;

            ?>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-bluefor f-w-600 p-2"><?php echo $porcentagemParticipacao . '%' ?></h4>
                                <h6 class="text-muted m-b-0 p-2">Taxa de participação</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-bluefor">
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

            $queryQTDProvas = "SELECT COUNT(DISTINCT eq.id_equipes) AS quantidade_equipes
            FROM provas AS p
            JOIN equipes_provas AS eq ON p.id = eq.id_provas
            JOIN sessoes AS ses ON eq.id_sessao = ses.id
            WHERE ses.id = :idSessaoSelecionada";

            $stmtqueryQTDProvas = $pdo->prepare($queryQTDProvas);
            $stmtqueryQTDProvas->bindParam(':idSessaoSelecionada', $idSessaoSelecionada, PDO::PARAM_INT);
            $stmtqueryQTDProvas->execute();
            $resultado = $stmtqueryQTDProvas->fetch(PDO::FETCH_ASSOC);

            $quantidadeEquipes = $resultado['quantidade_equipes'];

            echo "<div class='col-xl-3 col-md-6 mb-4'>
               <div class='card'>
                   <div class='card-block'>
                       <div class='row align-items-center'>
                           <div class='col-8'>
                               <h4 class='text-c-orange f-w-600 p-2'>" . $quantidadeEquipes . "</h4>
                               <h6 class='text-muted m-b-0 p-2'>Equipes</h6>
                           </div>
                           <div class='col-3 text-right'>
                               <i class='fa fa-bar-chart' aria-hidden='true'></i>
                           </div>
                       </div>
                   </div>
                   <div class='card-footer bg-c-orange'>
                       <div class='row align-items-center'>
                           <div class='col-9'>
                               <p class='text-white m-b-0'></p>
                           </div>
                           <div class='col-3 text-right'>
                               <i class='fa fa-area-chart' aria-hidden='true'></i>
                           </div>
                       </div>
                   </div>
               </div>
           </div>";
            ?>

            <?php

            try {
                $querySessao = "SELECT e.nome AS nome_equipe, MAX(p.ponto_obtido) AS maior_pontuacao
                FROM sessoes AS s
                JOIN pontuacao AS p ON s.id = p.id_sessoes
                JOIN equipes AS e ON e.id = p.id_equipes
                JOIN gerenciamento_sessao AS gs ON s.id = gs.id_sessoes
                WHERE gs.id_sessoes = :idSessaoSelecionada
                GROUP BY e.nome
                ORDER BY maior_pontuacao DESC, MIN(p.tempo_gasto) ASC
                LIMIT 1";

                $stmtSessao = $pdo->prepare($querySessao);
                $stmtSessao->bindParam(':idSessaoSelecionada', $idSessaoSelecionada, PDO::PARAM_INT);
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
                                <h4 class="text-c-blue f-w-600 p-2"><?php echo isset($equipeMaisPontos['maior_pontuacao']) ? number_format($equipeMaisPontos['maior_pontuacao'], 0, ',', '.') : '0'; ?></h4>
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

            $queryTempoTotal = "SELECT SUM(DISTINCT p.tempo_gasto) AS tempo_total
                    FROM pontuacao AS p
                    JOIN sessoes AS s ON p.id_sessoes = s.id
                    JOIN gerenciamento_sessao AS gs ON s.id = gs.id_sessoes
                    WHERE gs.id_sessoes = :idSessaoSelecionada
                    ";

            $stmtTempo = $pdo->prepare($queryTempoTotal);
            $stmtTempo->bindParam(':idSessaoSelecionada', $idSessaoSelecionada, PDO::PARAM_INT);
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

            <?php

            $IdSessaoSelecionada = null;
            $queryQtdProvras = "SELECT COUNT(DISTINCT eq.id_provas) AS equipes FROM equipes_provas AS eq
            JOIN sessoes AS ses ON eq.id_sessao  = ses.id
            JOIN gerenciamento_sessao AS gs ON gs.id_sessoes =  ses.id 
            WHERE gs.id_sessoes = :idSessaoSelecionada";
            $stmtQtdProvras = $pdo->prepare($queryQtdProvras);
            $stmtQtdProvras->bindParam(':idSessaoSelecionada', $idSessaoSelecionada, PDO::PARAM_INT);
            $stmtQtdProvras->execute();
            $QtdProvrasTotal = $stmtQtdProvras->fetch(PDO::FETCH_ASSOC);

            ?>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-orangets f-w-600 p-2"><?php echo $QtdProvrasTotal['equipes'] ?></h4>
                                <h6 class="text-muted m-b-0 p-2">Quantidade Provas</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-orangets">
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

            $tempoEmSegundos = strtotime($tempoFormatado) - strtotime('00:00:00');
            $quantidadeEquipes = $QtdProvrasTotal['equipes'];
            $tempoMedio = ($quantidadeEquipes != 0) ? $tempoEmSegundos / $quantidadeEquipes : 0;

            $tempoMedioFormatado = number_format($tempoMedio, 2);

            $horas = floor($tempoMedio / 3600);
            $minutos = floor(($tempoMedio / 60) % 60);
            $segundos = $tempoMedio % 60;
            $tempoMedioFormatadoHHMMSS = sprintf("%02d:%02d:%02d", $horas, $minutos, $segundos);

            ?>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card">
                    <div class="card-block">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="text-c-violeta f-w-600 p-2"><?php echo $tempoMedioFormatadoHHMMSS  ?></h4>
                                <h6 class="text-muted m-b-0 p-2">Tempo Medio Provas</h6>
                            </div>
                            <div class="col-3 text-right">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-c-violeta">
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