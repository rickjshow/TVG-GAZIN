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
            $queryFacilitadores = "SELECT COUNT(*) FROM tipo AS tipo
            JOIN usuarios AS u ON tipo.id = u.id_tipo
            JOIN gerenciamento_sessao AS gs ON u.id = gs.id_usuarios
            WHERE gs.id_sessoes = :idSessaoSelecionada AND u.permission = 'limited'";
            $stmtFacilitadores = $pdo->prepare($queryFacilitadores);
            $stmtFacilitadores->bindParam(':idSessaoSelecionada', $idSessaoSelecionada, PDO::PARAM_INT);
            $stmtFacilitadores->execute();
            $Facilitadores = $stmtFacilitadores->fetchAll(PDO::FETCH_ASSOC);

    
       echo "<div class='col-xl-3 col-md-6 mb-4'>
               <div class='card'>
                   <div class='card-block'>
                       <div class='row align-items-center'>
                           <div class='col-8'>
                               <h4 class='text-c-orange f-w-600 p-2'>" . $Facilitadores[0]['COUNT(*)'] . "</h4>
                               <h6 class='text-muted m-b-0 p-2'>Quantidade de Facilitadores</h6>
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
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</body>

</html>