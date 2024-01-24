<?php

$data = [];

include "conexao.php";
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
    <script src="temporizador.js"></script>
    <title>Lançar Pontos</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
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
                    <h1 id="temporizador">40:00</h1>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" id="iniciar" onclick="iniciarTemporizador()">Iniciar</button>
                    <button class="btn btn-secondary" id="pausar" onclick="pausarTemporizador()">Pausar</button>
                    <button class="btn btn-danger" id="finalizar" onclick="finalizarTemporizador()">Finalizar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tempoFinalEmSegundos']) && isset($_POST['idProva'])) {
    $idProva = $_POST['idProva'];
    $tempoTotal = json_decode($_POST['tempoFinalEmSegundos'], true);

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
    }

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
    }

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
    }

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
        $querySituacao = "UPDATE equipes_provas SET situacao = 'Finalizado' WHERE id_provas = :id_provas";
        $consulta4 = $pdo->prepare($querySituacao);
        $consulta4->bindValue(':id_provas', $idProva, PDO::PARAM_INT);
        $consulta4->execute();

        header("location: vivenciasPendentes.php");
        exit();

    } else {
        echo "Erro ao atualizar";
        exit;
    }
    
} 
 
?>


</body>

</html>
