<?php

include "conexao.php";
include "header.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $queryProva = "SELECT * FROM provas WHERE id = :id";
    $consulta = $pdo->prepare($queryProva);
    $consulta->bindParam(":id", $id, PDO::PARAM_INT);
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
                        <h3>Temporizador</h3>
                    </div>
                    <div class="card-body">
                        <h1 id="temporizador">40:00</h1>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" id="iniciar" onclick="iniciarTemporizador()">Iniciar</button>
                        <button class="btn btn-secondary" id="pausar" onclick="pausarTemporizador()">Pausar</button>
                        <button class="btn btn-danger" id="finalizar" onclick="finalizarTemporizador()">Finalizar</button>
                        <button class="btn btn-danger" id="resetar" onclick="resetarTemporizador()">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>