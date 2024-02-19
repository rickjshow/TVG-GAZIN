<?php

include "conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['iniciar']) && isset($_GET['idProva'])) {
        echo json_encode($_GET['iniciar']);

        $idVivencia = $_GET['idProva'];

        $username = $_SESSION['username'];

        $queryUser = "SELECT id FROM usuarios WHERE nome = :nome";
        $consulta = $pdo->prepare($queryUser);
        $consulta->bindValue(":nome", $username, PDO::PARAM_STR);
        $consulta->execute();
        $userId = $consulta->fetchColumn();
    
        if (!$userId) {
            echo "Usuário não encontrado.";
            exit;
        } else {
            $queryEquipeSessao = "SELECT s.id AS id_sessao, e.id AS id_equipe FROM gerenciamento_sessao AS gs
                JOIN equipes AS e ON gs.id_equipe = e.id
                JOIN sessoes AS s ON gs.id_sessoes = s.id
                JOIN usuarios AS u ON gs.id_usuarios = u.id
                WHERE u.id = :id";
            $consulta1 = $pdo->prepare($queryEquipeSessao);
            $consulta1->bindValue(":id", $userId, PDO::PARAM_INT);
            $consulta1->execute();
            $dadosSessao = $consulta1->fetchAll(PDO::FETCH_ASSOC);
    
            if (!$dadosSessao) {
                echo "Dados da sessão não encontrados.";
                exit;
            } else {
    
                foreach ($dadosSessao as $dados) {
                    $idSessao = $dados['id_sessao'];
                    $idEquipe = $dados['id_equipe'];
                }

                $Iniciar = "UPDATE equipes_provas SET andamento = 'Execultando' WHERE id_provas = :idProva AND id_equipes = :idEquipes AND id_sessao = :idSessao";
                $consultaIniciar = $pdo->prepare($Iniciar);
                $consultaIniciar->bindValue(':idProva', $idVivencia, PDO::PARAM_INT);
                $consultaIniciar->bindValue(':idEquipes', $idEquipe, PDO::PARAM_INT);
                $consultaIniciar->bindValue(':idSessao', $idSessao, PDO::PARAM_INT);
                $consultaIniciar->execute();
            }
        }
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tempoFinalEmSegundos']) && isset($_GET['idProva'])) {
   
    $idProva = $_GET['idProva'];
    $tempoTotal = json_decode($_GET['tempoFinalEmSegundos'], true);


    $tempoFormatado = gmdate("H:i:s", $tempoTotal);

    $username = $_SESSION['username'];

    $queryUser = "SELECT id FROM usuarios WHERE nome = :nome";
    $consulta = $pdo->prepare($queryUser);
    $consulta->bindValue(":nome", $username, PDO::PARAM_STR);
    $consulta->execute();
    $userId = $consulta->fetchColumn();

    if (!$userId) {
        echo "Usuário não encontrado.";
        exit;
    } else {
        $queryEquipeSessao = "SELECT s.id AS id_sessao, e.id AS id_equipe FROM gerenciamento_sessao AS gs
            JOIN equipes AS e ON gs.id_equipe = e.id
            JOIN sessoes AS s ON gs.id_sessoes = s.id
            JOIN usuarios AS u ON gs.id_usuarios = u.id
            WHERE u.id = :id";
        $consulta1 = $pdo->prepare($queryEquipeSessao);
        $consulta1->bindValue(":id", $userId, PDO::PARAM_INT);
        $consulta1->execute();
        $dadosSessao = $consulta1->fetchAll(PDO::FETCH_ASSOC);

        if (!$dadosSessao) {
            echo "Dados da sessão não encontrados.";
            exit;
        } else {

            foreach ($dadosSessao as $dados) {
                $idSessao = $dados['id_sessao'];
                $idEquipe = $dados['id_equipe'];
            }

            $tempoMaximo = 40 * 60;


            $queryPontoMax = "SELECT pontuacao_maxima AS ponto_max FROM provas WHERE id = :id";
            $consulta2 = $pdo->prepare($queryPontoMax);
            $consulta2->bindValue(":id", $idProva, PDO::PARAM_INT);
            $consulta2->execute();
            $pontuacaoMaxima = $consulta2->fetchAll(PDO::FETCH_ASSOC);

            if (!$pontuacaoMaxima) {
                echo "Pontuação máxima não encontrada.";
                exit;
            } else {
                foreach ($pontuacaoMaxima as $ponto) {
                    $pontoMaximo = $ponto['ponto_max'];
                }

                $pontuacao = round((1 - ($tempoTotal / $tempoMaximo)) * $pontoMaximo);
                $pontuacao = max(0, $pontuacao);

                
                $queryPonto = "INSERT INTO pontuacao (id_provas, id_sessoes, id_equipes, ponto_obtido, tempo_gasto) VALUES(:id_provas, :id_sessoes, :id_equipes, :ponto_obtido, :tempo_gasto)";
                $consulta3 = $pdo->prepare($queryPonto);
                $consulta3->bindValue(':id_provas', $idProva, PDO::PARAM_INT);
                $consulta3->bindValue(':id_sessoes', $idSessao, PDO::PARAM_INT);
                $consulta3->bindValue(':id_equipes', $idEquipe, PDO::PARAM_INT);
                $consulta3->bindValue(':ponto_obtido', $pontuacao, PDO::PARAM_INT);
                $consulta3->bindParam(':tempo_gasto', $tempoFormatado, PDO::PARAM_STR);

                if ($consulta3->execute()) {
                    $querySituacao = "UPDATE equipes_provas SET situacao = 'Finalizado', andamento = 'Finalizado' WHERE id_provas = :id_provas AND id_equipes = :id_equipes AND id_sessao = :id_sessao";
                    $consulta4 = $pdo->prepare($querySituacao);
                    $consulta4->bindValue(':id_provas', $idProva, PDO::PARAM_INT);
                    $consulta4->bindValue(':id_equipes', $idEquipe, PDO::PARAM_INT);
                    $consulta4->bindValue(':id_sessao', $idSessao, PDO::PARAM_INT);
                    $consulta4->execute();

                    echo json_encode(['pontuacao' => $pontuacao]);
                    exit;
                } else {

                    echo json_encode(['error' => 'Erro ao salvar pontuação.']);
                    exit;
                }
            }
        }
    }
}


$data = [];

include "header.php";

$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $queryProva = "SELECT p.*, tp.nome AS tipo_prova FROM provas AS p 
    JOIN tipo_provas AS tp ON p.tipo_provas_id = tp.id
    WHERE p.id = :id";
    $consulta = $pdo->prepare($queryProva);
    $consulta->bindValue(":id", $id, PDO::PARAM_INT);
    $consulta->execute();
    $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

}


if (!empty($data)) {

    $prova = $data[0];
    
    $tipo_prova = $prova['tipo_prova'];

    if ($tipo_prova == 'Automatico') {
   
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Lançar Pontos</title>
</head>

<body>

    <div class='container-fluid mt-4'>
        <?php
        foreach ($data as $row) {
            echo "
            <div class='accordion accordion-flush' id='accordionFlushExample'>
                <div class='card mt-4 col-10 mx-auto'>
                    <div class='accordion-item card-body text-center'>
                        <div class='accordion-header'>
                            <div class='accordion-button collapsed' data-bs-toggle='collapse' data-bs-target='#flush-collapseOne' aria-expanded='false' aria-controls='flush-collapseOne'>
                                {$row['nome']}
                            </div>
                        </div>
                        <hr class='my-3'> 
                        <div id='flush-collapseOne' class='accordion-collapse collapse' data-bs-parent='#accordionFlushExample'>
                            <div class='accordion-body text-left'>
                                <p>{$row['descricao']}</p>
                                <hr class='my-3'> 
                                <p>{$row['pergunta']}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }
        ?>

        <div class="row mt-4">
            <div class="col-md-6 offset-md-3">
                <div class="card text-center">
                    <div class="card-header">
                        <h3>Tempo Prova</h3>
                    </div>
                    <div class="card-body">
                        <h1 id="timer">40:00</h1>
                    </div>
                    <div class="card-footer">
                            <button id="startButton" class="btn btn-primary" onclick="startTimer()">Iniciar</button>
                            <button class="btn btn-secondary" onclick="pauseTimer()">Pausar</button>
                            <button class="btn btn-danger" onclick="stopTimer()">Finalizar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>

                $(document).ready(function(){
                $("#startButton").click(function(){
                    var iniciar = 'iniciar';
                    var idProva = <?php echo $id; ?>; 
                    $.ajax({
                        type: "GET",
                        url: "lancarPontos.php?iniciar=" + iniciar + "&idProva=" + idProva,
                        dataType: 'json',
                        contentType: 'application/json',
                        success: function(response) {
                            console.log(response);
                        }
                    });
                });
            });
    </script>


        <script>
            var timerInterval;
            var timerRunning = false;
            var initialTotalTimeInSeconds = 2400;
            var totalTimeInSeconds = localStorage.getItem('tempoRestante') || initialTotalTimeInSeconds;
            var initialTimeInSeconds = totalTimeInSeconds;

            function startTimer() {
                if (!timerRunning) {
                    timerInterval = setInterval(updateTimer, 1000);
                    timerRunning = true;
                    localStorage.setItem('timerRunning', 'true');
                }
            }

            function pauseTimer() {
                if (!timerRunning) {
                    Swal.fire({
                        title: 'Aviso',
                        text: 'O temporizador já está pausado.',
                        icon: 'info'
                    });
                    return;
                }
                clearInterval(timerInterval);
                timerRunning = false;
                localStorage.setItem('timerRunning', 'false');
                localStorage.setItem('isPaused', 'true');
            }

            function saveTimerState(totalTimeInSeconds, tempoGasto) {
                localStorage.setItem('tempoRestante', totalTimeInSeconds);
                localStorage.setItem('tempoGasto', tempoGasto);
            }

            function restoreTimerState() {
                var tempoArmazenado = localStorage.getItem('tempoRestante');
                var timerRunning = localStorage.getItem('timerRunning'); 

                if (tempoArmazenado) {
                    totalTimeInSeconds = parseInt(tempoArmazenado);
                    updateDisplay();
                    if (totalTimeInSeconds > 0 && timerRunning === 'true') { 
                        startTimer(); 
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                restoreTimerState();
            });

            function enviarPontos(){
                var tempoGasto = initialTotalTimeInSeconds - totalTimeInSeconds;
                var idProva = <?php echo $id; ?>;
                        $.ajax({
                            type: "GET",
                            url: "lancarPontos.php?tempoFinalEmSegundos=" + tempoGasto + "&idProva=" + idProva, 
                            dataType: 'json', 
                            contentType: 'application/json',
                            success: function(response) {
                                console.log(response);
                                if (response.pontuacao) {
                                    var pontuacao = response.pontuacao;

                                    Swal.fire({
                                        title: 'Pontuação Final',
                                        text: 'Sua pontuação final é: ' + pontuacao,
                                        icon: 'success',
                                        showCancelButton: false,
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = 'vivenciasPendentes.php';
                                        }
                                    });

                                } else if (response.error) {
                                    console.error(response.error);
                                    Swal.fire({
                                        title: 'Erro',
                                        text: 'Ocorreu um erro ao processar a pontuação.',
                                        icon: 'error'
                                    });
                                } else {
                                    console.error("Resposta inesperada do servidor:", response);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Erro na requisição AJAX: " + error);
                                Swal.fire({
                                    title: 'Erro',
                                    text: 'Ocorreu um erro na requisição AJAX.',
                                    icon: 'error'
                                });
                            }
                        });

                    localStorage.removeItem('tempoRestante');

                    totalTimeInSeconds = initialTotalTimeInSeconds;
                    updateDisplay();
                }

            function stopTimer() {

                if (!timerRunning) {
                    Swal.fire({
                        title: 'Aviso',
                        text: 'É necessário iniciar o temporizador antes de finalizar.',
                        icon: 'info'
                    });
                    return;
                }

                clearInterval(timerInterval);
                timerRunning = false;
                updateDisplay();
                showTimeSpentAlert();

                if (totalTimeInSeconds === 0) {

                    Swal.fire({
                        title: 'Tempo Esgotado',
                        text: 'O tempo acabou!',
                        icon: 'info'
                    }).then(() => {
                        enviarPontos();
                    });
                    } else {
                    Swal.fire({
                        title: 'Você tem certeza?',
                        text: 'Isso finalizará o temporizador e lançará os pontos.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sim, finalizar!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            enviarPontos();
                        } else {
                            if (!timerRunning) {
                                startTimer();
                            }
                        }
                    });
                }
            }

            function updateTimer() {
                if (totalTimeInSeconds === 0) {
                    clearInterval(timerInterval);
                    stopTimer();
                } else {
                    totalTimeInSeconds--;
                    tempoGasto = initialTotalTimeInSeconds - totalTimeInSeconds;
                    localStorage.setItem('tempoRestante', totalTimeInSeconds);
                    updateDisplay();
                }
            }

            function updateDisplay() {
                var minutes = Math.floor(totalTimeInSeconds / 60);
                var seconds = totalTimeInSeconds % 60;
                var formattedTime = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                document.getElementById('timer').innerText = formattedTime;
            }

            function showTimeSpentAlert() {
                var minutes = Math.floor(tempoGasto / 60);
                var seconds = tempoGasto % 60;
                var formattedTimeSpent = `${minutes} minutos e ${seconds} segundos`;
                Swal.fire({
                    title: 'Tempo Gasto',
                    text: 'Você gastou ' + formattedTimeSpent + ' nesta prova.',
                    icon: 'info'
                });
            }

            window.onbeforeunload = function(event) {
                if (event.target.performance.navigation.type !== 1) {
                    // Sempre salve o tempo restante
                    localStorage.setItem('tempoRestante', totalTimeInSeconds);
                
                }
            };

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

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />

</body>

</html>
<?php
} elseif($tipo_prova == 'Manual_comida'){

    header("location: adicionar_prova_manual_gastronomica.php?id={$id}");
    exit;

}elseif($tipo_prova == 'Manual_estilingue'){
   

    header("location: adicionar_prova_manual_estilingue.php?id={$id}"); 
    exit;

} else {
    echo "Tipo de prova desconhecido: " . $tipo_prova;
}

}