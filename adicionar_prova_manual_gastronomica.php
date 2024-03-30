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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   
    $idProva = $_POST['idProva'];
    $tempoTotal = $_POST['tempoFinalEmSegundos'];
    $sabor = $_POST['sabor'];
    $atendimento = $_POST['atendimento'];
    $organizacao = $_POST['organizacao'];

    $pontuacao = ($sabor) + ($atendimento) + ($organizacao);

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

                $queryPontoMax = "SELECT pontuacao_maxima AS ponto_max FROM provas WHERE id = :id";
                $consulta2 = $pdo->prepare($queryPontoMax);
                $consulta2->bindValue(":id", $idProva, PDO::PARAM_INT);
                $consulta2->execute();
                $pontuacaoMaxima = $consulta2->fetchAll(PDO::FETCH_ASSOC);
    
                    $excedeMaximo = false;

                        foreach ($pontuacaoMaxima as $ponto) {
                            $pontoMaximo = $ponto['ponto_max'];
                        
                            if ($pontuacao > $pontoMaximo) {
                                $excedeMaximo = true;
                                break;
                            }
                        }               

                        if ($excedeMaximo) {
                            echo json_encode(['error' => 'A pontuação não pode exceder o ' . $pontoMaximo . ' máximo permitido.']);
                            exit;                            
                        } else {

                            $mediaPontos = ($sabor + $atendimento + $organizacao) / 3;

                            $pontuacaoFinal = round($mediaPontos / 10 * $pontoMaximo);

                            $queryPonto = "INSERT INTO pontuacao (id_provas, id_sessoes, id_equipes, ponto_obtido, tempo_gasto) VALUES(:id_provas, :id_sessoes, :id_equipes, :ponto_obtido, :tempo_gasto)";
                            $consulta3 = $pdo->prepare($queryPonto);
                            $consulta3->bindValue(':id_provas', $idProva, PDO::PARAM_INT);
                            $consulta3->bindValue(':id_sessoes', $idSessao, PDO::PARAM_INT);
                            $consulta3->bindValue(':id_equipes', $idEquipe, PDO::PARAM_INT);
                            $consulta3->bindValue(':ponto_obtido', $pontuacaoFinal, PDO::PARAM_INT);
                            $consulta3->bindParam(':tempo_gasto', $tempoFormatado, PDO::PARAM_STR);
        
                            if ($consulta3->execute()) {
                                $querySituacao = "UPDATE equipes_provas SET situacao = 'Finalizado', andamento = 'Finalizado' WHERE id_provas = :id_provas AND id_equipes = :id_equipes AND id_sessao = :id_sessao";
                                $consulta4 = $pdo->prepare($querySituacao);
                                $consulta4->bindValue(':id_provas', $idProva, PDO::PARAM_INT);
                                $consulta4->bindValue(':id_equipes', $idEquipe, PDO::PARAM_INT);
                                $consulta4->bindValue(':id_sessao', $idSessao, PDO::PARAM_INT);
                                $consulta4->execute();
        
                                echo json_encode(['pontuacao' => 'A sua pontuação foi de ' . $pontuacaoFinal . ' pontos!']);
                                exit;
                            } else {     
                                echo json_encode(['error' => 'Erro ao salvar pontuação.']);
                                exit;
                        }
                    }                 
                }
            }
        }

include "header.php";

$data = [];

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
            <div class='accordion accordion-flush' id='accordionFlushExample '>
                <div class='card mt-4 col-10 mx-auto'>
                    <div class='accordion-item card-body text-center'>
                        <div class='accordion-header '>
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

        <div id="temporizador" class="row mt-4">
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
                            <button class="btn btn-danger" onclick="stopTimer()">Encerrar </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-10 mx-auto mt-4">
            <div class="mt-4 text-center p-4 border rounded shadow">
                <h4 class="mt-4 font-weight-bold text-primary" style="font-size: 14px;">Adicione a pontuação de 0 a 10 em cada campo (apenas numeros inteiros)</h4>
            </div>
        </div>
       
        <div class="row mt-5">
            <div class="col-md-6 offset-md-3">
                <form id="pontuacaoForm" style="display: none;">
                    <div class="form-group">
                        <label for="sabor">Pontuação para o Sabor:</label>
                        <input type="number" class="form-control" id="sabor" name="sabor" min="0" max="10" oninput="validarPontuacao(event)" required>
                        <small class="error-message text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="atendimento">Pontuação para o Atendimento:</label>
                        <input type="number" class="form-control" id="atendimento" name="atendimento" min="0" max="10" oninput="validarPontuacao(event)" required>
                        <small class="error-message text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label for="organizacao">Pontuação para a Organização:</label>
                        <input type="number" class="form-control" id="organizacao" name="organizacao" min="0" max="10" oninput="validarPontuacao(event)" required>
                        <small class="error-message text-danger"></small>
                    </div>
                    <button type="button" class="btn btn-danger" onclick="enviarPontos()">Finalizar Prova</button>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function(){
                $("#startButton").click(function(){
                    var iniciar = 'iniciar';
                    var idProva = <?php echo $id; ?>; 
                    $.ajax({
                        type: "GET",
                        url: "adicionar_prova_manual_gastronomica.php?iniciar=" + iniciar + "&idProva=" + idProva,
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
    var tempoGasto;

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
    }

    function saveTimerState(totalTimeInSeconds, formVisible, tempoGasto) {
        localStorage.setItem('tempoRestante', totalTimeInSeconds);
        localStorage.setItem('formVisivel', formVisible);
        localStorage.setItem('tempoGasto', tempoGasto);
    }

        function restoreTimerState() {
        var tempoArmazenado = localStorage.getItem('tempoRestante');
        var formVisivel = localStorage.getItem('formVisivel');
        var timerRunning = localStorage.getItem('timerRunning'); 

        if (tempoArmazenado) {
            totalTimeInSeconds = parseInt(tempoArmazenado);
            updateDisplay();
            if (totalTimeInSeconds > 0 && timerRunning === 'true') {
                startTimer(); 
            }
        }
        if (formVisivel === 'true') {
            showPontuacaoForm();
        }
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
            Swal.fire({
            title: 'Confirmação',
            text: 'Tem certeza de que deseja finalizar o temporizador?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                clearInterval(timerInterval);
                timerRunning = false;
                updateDisplay();
                tempoGasto = initialTotalTimeInSeconds - totalTimeInSeconds;
                showTimeSpentAlert();
                totalTimeInSeconds = initialTotalTimeInSeconds;
                updateDisplay();
                showPontuacaoForm();
                saveTimerState(initialTotalTimeInSeconds, true, tempoGasto);
            }
        });
    }

    function showPontuacaoForm() {
        document.getElementById('pontuacaoForm').style.display = 'block';
        document.getElementById('temporizador').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        restoreTimerState();
    });

    function validarPontuacao(event) {
        const input = event.target;
        let valor = input.value.replace(/\D/g, ''); // Remove todos os caracteres que não são dígitos
        const mensagemErroElement = input.parentElement.querySelector('.error-message');

        if (valor !== '') {
            valor = parseInt(valor); // Converte o valor para um número inteiro

            if (isNaN(valor) || valor < 0 || valor > 10) {
                mensagemErroElement.textContent = 'Por favor, insira um número inteiro de 0 a 10.';
                input.value = ''; // Limpa o campo se o valor estiver fora do intervalo permitido
            } else {
                mensagemErroElement.textContent = '';
            }
        }
    }




    function enviarPontos() {
        var idProva = <?php echo $id; ?>;
        var sabor = document.getElementById('sabor').value;
        var atendimento = document.getElementById('atendimento').value;
        var organizacao = document.getElementById('organizacao').value;

        if (sabor === '' || atendimento === '' || organizacao === '' || tempoGasto === 0) {
            Swal.fire({
                title: 'Aviso',
                text: 'Por favor, preencha todas as pontuações antes de finalizar a prova.',
                icon: 'info'
            });
            return;
        }

        $.ajax({
            type: "POST",
            url: "adicionar_prova_manual_gastronomica.php",
            data: {
                tempoFinalEmSegundos: localStorage.getItem('tempoGasto'),
                idProva: idProva,
                sabor: sabor,
                atendimento: atendimento,
                organizacao: organizacao
            },
            success: function (response) {
                console.log(response);
                try {

                    var responseData = JSON.parse(response);

                    if (responseData.error) {
                        Swal.fire({
                            title: 'Erro',
                            text: responseData.error,
                            icon: 'error',
                            showCancelButton: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            window.location.reload();
                        });
                    }
                    if (responseData.pontuacao) {
                        Swal.fire({
                            title: 'Pontuação Final',
                            text: responseData.pontuacao,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                localStorage.clear();
                                window.location.href = 'vivenciasPendentes.php';
                            }
                        });
                    }
                } catch (error) {
                    console.error("Erro ao processar resposta JSON:", error);
                }
            },
            error: function (xhr, status, error) {
                console.error("Erro na requisição AJAX:", error);
                Swal.fire({
                    title: 'Erro',
                    text: 'Erro na requisição AJAX.',
                    icon: 'error',
                    showCancelButton: false,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }
        });

        localStorage.removeItem('tempoRestante');
        totalTimeInSeconds = initialTotalTimeInSeconds;
        updateDisplay();
    }

    function updateTimer() {
        if (totalTimeInSeconds === 0) {
            clearInterval(timerInterval);
            showTimeIsUpAlert(); 
            showPontuacaoForm();
            return; 
        }

        totalTimeInSeconds--;
        tempoGasto = initialTotalTimeInSeconds - totalTimeInSeconds;
        localStorage.setItem('tempoRestante', totalTimeInSeconds);
        updateDisplay();
    }

        function showTimeIsUpAlert() {
        Swal.fire({
            title: 'Tempo Esgotado',
            text: 'O tempo acabou!',
            icon: 'info'
        }).then(() => {
            clearInterval(timerInterval);
                timerRunning = false;
                updateDisplay();
                tempoGasto = initialTotalTimeInSeconds - totalTimeInSeconds;
                showTimeSpentAlert();
                totalTimeInSeconds = initialTotalTimeInSeconds;
                updateDisplay();
                showPontuacaoForm();
                saveTimerState(initialTotalTimeInSeconds, true, tempoGasto);
        });
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

    console.log(localStorage);


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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet"/>
</body>
</html>