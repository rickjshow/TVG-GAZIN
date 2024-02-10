<?php

require_once("conexao.php");
require_once "permissao.php";
include "temporizador.php";
require_once "header.php";

verificarPermissao($permission);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sqlProvas = "
        SELECT p.*, tp.nome AS tipo_provas FROM provas AS p 
        JOIN tipo_provas AS tp ON p.tipo_provas_id = tp.id
        WHERE p.id = :id;
    ";

    $consulta = $pdo->prepare($sqlProvas);
    $consulta->bindParam(':id', $id, PDO::PARAM_INT);
    $consulta->execute();

    if (!$consulta) {
        die("Consulta falha");
    }

    $row = $consulta->fetch(PDO::FETCH_ASSOC);


    if (!$row) {
        die("Não foi possível recuperar os dados do banco de dados:<br> 
         Erro login: " . print_r($consulta->errorInfo(), true));
    }
}

if (isset($_POST['update_prova'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $pergunta = $_POST['pergunta'];
    $pontos = $_POST['pontos'];
    $tipo_provas = $_POST['tipo_prova'];

    $queryTipo = "SELECT id FROM tipo_provas WHERE nome = :nome";
    $consultaTipo = $pdo->prepare($queryTipo);
    $consultaTipo->bindValue(':nome', $tipo_provas);
    $consultaTipo->execute();
    $resultado_tipo =  $consultaTipo->fetch(PDO::FETCH_ASSOC);

    $sqlprova = "
    UPDATE provas
    SET
        nome = :nome,
        descricao = :descricao,
        pergunta = :pergunta,
        pontuacao_maxima = :pontos,
        tipo_provas_id = :tipo_prova
    WHERE id = :id 
    ";

    $consulta = $pdo->prepare($sqlprova);
    $consulta->bindValue(':nome', $nome);
    $consulta->bindValue(':descricao', $descricao);
    $consulta->bindValue(':pergunta', $pergunta);
    $consulta->bindValue(':pontos', $pontos);
    $consulta->bindValue(':tipo_prova',  $resultado_tipo['id'], PDO::PARAM_INT);
    $consulta->bindValue(':id', $id, PDO::PARAM_INT);

    if ($consulta->execute()) {
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Atualizado sucesso!');
        header("location: cadastro_provas.php");
        exit();
    } else {
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Falha ao atualizar prova');
        header("location: cadastro_provas.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>UpdateProva</title>
</head>
<body>


<div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold display-4 text-primary" style="font-size: 15px;">Atualizar Vivência</h3>
        </div>
</div>
    
<div class="container">
    <div class="container mt-4 border rounded p-4 shadow">
        <div class="mx-auto col-md-6">
            <form action="updateProva.php" method="post">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <div class="form-group">
                    <label for="nome" class="mt-4">Nome da Vivência:</label>
                    <input type="text" name="nome" class="form-control" value="<?= $row['nome'] ?>">
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea class="form-control" name="descricao" rows="5"><?= $row['descricao'] ?></textarea>
                </div>

                <div class="form-group">
                    <label for="pergunta">Perguntas:</label>
                    <textarea class="form-control" name="pergunta" rows="5"><?= $row['pergunta'] ?></textarea>
                </div>

                <div class="form-group">
                    <label for="pontos">Pontuação Máxima da Prova:</label>
                    <input type="number" name="pontos" class="form-control" value="<?= $row['pontuacao_maxima'] ?>">
                </div>

                <div class="form-group">
                <label for="tipo_provas">Tipo Prova:</label>
                    <select name="tipo_prova" class="form-control">
                <?php 
                    
                    $queryTipoAll = "SELECT * FROM tipo_provas";
                    $consultaTipo = $pdo->prepare($queryTipoAll);
                    $consultaTipo->execute();
                    $data = $consultaTipo->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($data as $tipo) : ?>
                        <option value="<?= $tipo['nome'] ?>" <?= ($row['tipo_provas'] == $tipo['nome']) ? "selected" : "" ?>>
                            <?= $tipo['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

                <input type="submit" style="font-size: 12px" class="btn btn-success" name="update_prova" value="Atualizar">
            </form>
            <div class="form-group">
                <button id="btnExcluirProva" class="btn btn-danger" style="font-size: 12px; margin-left:90px; margin-top:-59px">Excluir</button>
            </div> 
        </div>
    <script>

    $(document).ready(function() {
        $("#btnExcluirProva").prop("disabled", false)
        $("#btnExcluirProva").click(function() {
            var idProva = "<?php echo $id; ?>"
            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Esta ação irá excluir a prova. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'deleteProva.php',
                        data: { idProva: idProva},
                        success: function(response) {
                            window.location.href = 'deleteProva.php?idProva=' + idProva;
                        },
                        error: function(error) {
                            console.error('Erro ao excluir a Prova:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao excluir a prova. Por favor, tente novamente.'
                            });
                        }
                    });
                }
            });
        });
    });

    </script>
    </div>
</div>
<div class="text-center mt-4"></div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>