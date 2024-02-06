<?php

require_once "conexao.php";
include "header.php";
include "temporizador.php";

$username = $_SESSION['username'];

$queryUser = "SELECT id, permission FROM usuarios WHERE nome = :username";
$stmtUser = $pdo->prepare($queryUser);
$stmtUser->bindParam(":username", $username);
$stmtUser->execute();

$resultUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

$querySessao = "SELECT nome, id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
$stmtSessao = $pdo->prepare($querySessao);
$stmtSessao->execute();
$nomeSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);


if ($resultUser['permission'] == 'limited') {
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vivencias Pendentes</title>
    </head>

    <body>
        <div class="box1 mt-4 text-center">
            <h1 class='mt-4' style='font-size: 20px;'>Vivências Pendentes</h1>
            <h4 class='mt-4'></h4>
            <h4 class='mt-4 text-center mx-auto' style='background-color: #163387; color: white; max-width: 400px; font-size: 1.3em; padding:5px; border:solid #000;'> Sessão Atual: <?php echo $nomeSessao['nome']; ?></h4>
        </div>
        <div class="container-fluid text-center">

            <?php

            if (isset($_SESSION['alerta'])) {
                echo "<script>
                    alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                </script>";
                unset($_SESSION['alerta']);
            }

            ?>

            <?php

            $idSessao = $nomeSessao['id'];

            if ($resultUser) {
                $userId = $resultUser['id'];
                $queryProvas = "SELECT DISTINCT e.nome AS equipe_nome, p.nome AS prova_nome, p.id AS prova_id FROM equipes_provas AS ep
                    JOIN provas AS p ON  ep.id_provas = p.id
                    JOIN equipes AS e ON ep.id_equipes = e.id
                    JOIN gerenciamento_sessao AS gs ON e.id = gs.id_equipe
                    JOIN usuarios AS u ON gs.id_usuarios = u.id
                    JOIN sessoes AS ses ON ep.id_sessao = ses.id
                    WHERE u.id = :userId AND ses.situacao = 'Pendente' AND ep.situacao = 'Pendente' AND ep.id_sessao = :id_sessao AND gs.id_sessoes = :id_sessao";

                $consulta = $pdo->prepare($queryProvas);
                $consulta->bindParam(":userId", $userId);
                $consulta->bindParam(":id_sessao", $idSessao);
                $consulta->execute();
                $data = $consulta->fetchAll(PDO::FETCH_ASSOC);



                foreach ($data as $row) {
                    echo "<div class='row'>
                          <div class='col-md-6 mx-auto  mt-4 '>
                              <div class='border rounded shadow'>
                                  <a href='lancarPontos.php?id={$row['prova_id']}' style='text-decoration: none; color: black;'>
                                      <div class='card mt-4 border-0'> <!-- Add border-0 class here -->
                                          <div class='card-body text-center'>
                                              <h5 class='card-title'>{$row['equipe_nome']}</h5>
                                              <p class='card-text'>{$row['prova_nome']}</p>
                                          </div>
                                      </div>
                                  </a>
                              </div>
                          </div>
                      </div>";
                }
            }

            ?>

            <script>
                resetTimer();
            </script>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
    </body>

    </html>
<?php
} else {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Document</title>
    </head>
        <body>";



    $idSessao = $nomeSessao['id'];

    if ($resultUser) {
        $userId = $resultUser['id'];
        $queryProvas = "SELECT DISTINCT e.nome AS equipe_nome, p.nome AS prova_nome, p.id AS prova_id, ep.situacao AS situacao, ep.andamento AS andamento FROM equipes_provas AS ep
                    JOIN provas AS p ON  ep.id_provas = p.id
                    JOIN equipes AS e ON ep.id_equipes = e.id
                    JOIN gerenciamento_sessao AS gs ON e.id = gs.id_equipe
                    JOIN usuarios AS u ON gs.id_usuarios = u.id
                    JOIN sessoes AS ses ON ep.id_sessao = ses.id
                    WHERE ses.situacao = 'Pendente' AND ep.id_sessao = :id_sessao AND gs.id_sessoes = :id_sessao";

        $consulta = $pdo->prepare($queryProvas);
        $consulta->bindParam(":id_sessao", $idSessao);
        $consulta->execute();
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

        echo "<div class='mt-4'></div>";
        echo "<h1 class='font-weight-bold mt-4 text-center' style='font-size: 20px;'>Vivências Pendentes</h1>
            <h4 class='mt-4 text-center mx-auto' style='background-color: #163387; color: white; max-width: 400px; font-size: 1.3em; padding:5px; border:solid #000;'> Sessão Atual: {$nomeSessao["nome"]} </h4>";

        $equipesProcessadas = [];

        foreach ($data as $row) {
            if (!in_array($row['equipe_nome'], $equipesProcessadas)) {
                echo "<div class='container-fluid'>";
                echo "<div class='row'>
                              <div class='col-md-8 mx-auto my-2 mt-4'> 
                                  <div class='border rounded shadow'>
                                      <h4 class='text-center mt-3 mb-0'>{$row['equipe_nome']}</h4>";


                $equipesProcessadas[] = $row['equipe_nome'];

               

                foreach ($data as $prova) {
                    if ($prova['equipe_nome'] === $row['equipe_nome']) {
                        $icone_cor = '';
                        $icone = '';


                        if ($prova['situacao'] === 'Pendente' && $prova['andamento'] === 'Aguardando') {
                            $icone_cor = 'text-danger';
                            $icone = 'fa-times';
                            $statusText = 'Aguardando';
                        } elseif ($prova['andamento'] === 'Execultando') {
                            $icone_cor = 'text-warning';
                            $icone = 'fa-hourglass';
                            $statusText = 'Em andamento'; 
                        } elseif ($prova['situacao'] === 'Finalizado' && $prova['andamento'] === 'Finalizado') {
                            $icone_cor = 'text-success';
                            $icone = 'fa-check';
                            $statusText = 'Finalizado'; 
                        }


                        echo "<div class='card border-0'>
                                        <div class='card-body d-flex justify-content-between align-items-center p-3'>
                                            <h5 class='card-title m-0'>{$prova['prova_nome']}</h5>
                                            <span class='badge {$icone_cor} ml-2'>{$statusText}</span>
                                            <i class='fas {$icone} ml-2'></i>
                                        </div>
                                    </div>";

                    }
                }

                echo "</div></div></div></div>";
                echo "<div class='mt-4'></div>";
            }
        }
    }

    echo "</body>
    </html>";
}

?>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">