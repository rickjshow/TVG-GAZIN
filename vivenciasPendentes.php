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

    <?php  

    if(isset($nomeSessao['id'])){
        $idSessao = $nomeSessao['id']; 
    }else{
        $idSessao = null;
    }
    
    if(isset($nomeSessao['nome'])){
        $nomeSession = $nomeSessao['nome'];
    }else{
        $nomeSession = null;
    }

    ?>

    <body>
        <div class="box1 mt-4 text-center">
            <h1 class='mt-4' style='font-size: 20px;'>Vivências Pendentes</h1>
            <h4 class='mt-4'></h4>
            <h4 class='mt-4 text-center mx-auto' style='background-color: #163387; color: white; max-width: 400px; font-size: 1.3em; padding:5px; border:solid #000;'> Sessão Atual: <?php echo $nomeSession; ?></h4>
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

        
        if(isset($nomeSessao['nome'])){
            $nomeSession = $nomeSessao['nome'];
        }else{
            $nomeSession = null;
        }

        echo "<div class='mt-4'></div>";
        echo "<h1 class='font-weight-bold mt-4 text-center' style='font-size: 20px;'>Vivências Pendentes</h1>";
        echo "<h4 class='mt-4 text-center mx-auto' style='background-color: #163387; color: white; max-width: 400px; font-size: 1.3em; padding:5px; border:solid #000;'> Sessão Atual: $nomeSession </h4>";


        echo "<div id='vivencias-container'></div>";


    echo "</body>
    </html>";
}

?>

<style>

@keyframes loading {
    0% { content: 'Pendente.'; }
    25% { content: 'Pendente..'; }
    50% { content: 'Pendente...'; }
    75% { content: 'Pendente....'; }
    100% { content: 'Pendente.....'; }
}

.loading-text::after {
    content: 'Em andamento...';
    animation: loading 2s infinite;
}

</style>



<script>
    $(document).ready(function() {

        function atualizarVivencias() {

            $.ajax({
                url: 'atualizarVivencas.php', 
                method: 'GET',
                dataType: 'html',
                success: function(response) {
                    $('#vivencias-container').html(response);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        setInterval(atualizarVivencias, 2000);
    });
</script>

 <!--2024-02-07 2024-02-07 19:28:42-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">