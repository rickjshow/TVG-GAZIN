<?php

include "conexao.php";


//mudei pra GET pra facilitar depos pode volta pra post see quiser!!
//quando chama via ajax ta caindo aqui dentro !!!
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tempoFinalEmSegundos']) && isset($_GET['idProva'])) {

    //print_r($_GET);die; //tem para aqui agr
    //nego ta esperto kkkk
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

                //vc fez certo aqui so tava errado ordem que vc montou as coisa vamo ve se funga
                if ($consulta3->execute()) {
                    $querySituacao = "UPDATE equipes_provas SET situacao = 'Finalizado' WHERE id_provas = :id_provas AND id_equipes = :id_equipes AND id_sessao = :id_sessao";
                    $consulta4 = $pdo->prepare($querySituacao);
                    $consulta4->bindValue(':id_provas', $idProva, PDO::PARAM_INT);
                    $consulta4->bindValue(':id_equipes', $idEquipe, PDO::PARAM_INT);
                    $consulta4->bindValue(':id_sessao', $idSessao, PDO::PARAM_INT);
                    $consulta4->execute();

                    echo json_encode(['pontuacao' => $pontuacao]);
                    exit;
                } else {
                    // Se algo deu errado...
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

    $queryProva = "SELECT * FROM provas WHERE id = :id";
    $consulta = $pdo->prepare($queryProva);
    $consulta->bindValue(":id", $id, PDO::PARAM_INT);
    $consulta->execute();
    $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
}

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
                <div class='card mt-4'>
                    <div class='accordion-item card-body text-center'>
                        <div class='accordion-header'>
                            <div class='accordion-button collapsed' data-bs-toggle='collapse' data-bs-target='#flush-collapseOne' aria-expanded='false' aria-controls='flush-collapseOne'>
                                {$row['nome']}
                            </div>
                        </div>
                        <hr class='my-3'> <!-- Adiciona o divisor -->
                        <div id='flush-collapseOne' class='accordion-collapse collapse' data-bs-parent='#accordionFlushExample'>
                            <div class='accordion-body text-left'>
                                <p>{$row['descricao']}</p>
                                <hr class='my-3'> <!-- Adiciona o divisor -->
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
                        <button class="btn btn-primary" onclick="startTimer()">Iniciar</button>
                        <button class="btn btn-secondary" onclick="pauseTimer()">Pausar</button>
                        <button class="btn btn-danger" onclick="stopTimer()">Finalizar</button>
                    </div>
                </div>
            </div>
        </div>


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
                }
            }

            function pauseTimer() {

                if (!timerRunning) {
                    // Se o temporizador não estiver em execução, exibir mensagem
                    Swal.fire({
                        title: 'Aviso',
                        text: 'É necessário iniciar o temporizador antes de pausar.',
                        icon: 'info'
                    });
                    return;
                }

                clearInterval(timerInterval);
                timerRunning = false;
            }


            function stopTimer() {

                if (!timerRunning) {
                    // Se o temporizador não estiver em execução, exibir mensagem
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

                var tempoGasto = initialTotalTimeInSeconds - totalTimeInSeconds;
                var idProva = <?php echo $id; ?>;

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
                        $.ajax({
                            type: "GET",
                            url: "lancarPontos.php?tempoFinalEmSegundos=" + tempoGasto + "&idProva=" + idProva, //isso podia ser em outro arquivo ai vc mata aquele codico la cima joga pra outro arquvo 
                            dataType: 'json', // Adicione isso para indicar que você espera uma resposta JSON
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
                    } else {
                        if (!timerRunning) {
                            startTimer();
                        }
                    }
                });
            }

            function updateTimer() {
                if (totalTimeInSeconds === 0) {
                    clearInterval(timerInterval);
                    timerRunning = false;
                } else {
                    totalTimeInSeconds--;
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
                var timeSpentInSeconds = initialTotalTimeInSeconds - totalTimeInSeconds;
                var minutes = Math.floor(timeSpentInSeconds / 60);
                var seconds = timeSpentInSeconds % 60;

                var formattedTimeSpent = `${minutes} minutos e ${seconds} segundos`;

            }

            window.onbeforeunload = function(event) {

                if (event.target.performance.navigation.type !== 1) {
                    localStorage.setItem('tempoRestante', totalTimeInSeconds);
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                var tempoArmazenado = localStorage.getItem('tempoRestante');
                if (tempoArmazenado) {
                    totalTimeInSeconds = parseInt(tempoArmazenado);
                    updateDisplay();
                    if (totalTimeInSeconds > 0 && !timerRunning) {
                        startTimer();
                    }
                }
            });
        </script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />

</body>

</html>