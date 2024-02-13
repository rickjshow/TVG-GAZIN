<?php

include "conexao.php";
include "header.php";
include "temporizador.php";

$queryEdicao = "SELECT id FROM sessoes WHERE situacao = 'Pendente'";
$resultadoEdicao = $pdo->prepare($queryEdicao);
$resultadoEdicao->execute();
$Edicao = $resultadoEdicao->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alert.js"></script>
    <title>Document</title>
</head>
<body>

    <style>
        .fixed-size-image {
            width: 200px; 
            height: 200px; 
            object-fit: cover; 
        }
    </style>

    <div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Galeria de Fotos</h3>
                <?php 
                    if(isset($Edicao) && !empty($Edicao)){
                        echo "<button class='btn btn-primary mt-4' data-toggle='modal' style='font-size: 15px;' data-target='#importModal'>Adicionar Imagens</button>";            
                    }else{
                      echo"<p class='mt-4'>Sem TVG pendente no momento!</p>";  
                    }                
                ?>
        </div>
    </div>
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Anexar Fotos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <form action="adicionarFotos.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="fotos">Escolher Fotos:</label>
                        <input type="file" class="form-control-file" name="fotos[]" id="fotos" accept="image/*" multiple>
                    </div>
                    <button type="submit" id="arquivo" class="btn btn-primary" name="submit">Anexar</button>
                </form>
                    <?php
                        if (isset($_SESSION['alerta'])) {
                                echo "<script>";
                                echo "alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');";
                                echo "</script>";
                        unset($_SESSION['alerta']);
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php

            $querySessao = "SELECT id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
            $stmtSessao = $pdo->prepare($querySessao);
            $stmtSessao->execute();
            $IdSession = $stmtSessao->fetch(PDO::FETCH_ASSOC); 

            if(isset($IdSession)){
                $id = $IdSession['id'];
            }

            $queryImagem = "SELECT imagem FROM galeria WHERE id_sessoes = :id_sessoes";
            $stmtimagem = $pdo->prepare($queryImagem);
            $stmtimagem->bindParam(':id_sessoes', $id);
            $stmtimagem->execute();
            if ($stmtimagem->rowCount() > 0) {
                // Recupera todas as imagens retornadas da consulta
                $fotos = $stmtimagem->fetchAll(PDO::FETCH_ASSOC);

                echo '<div class="container mt-4">
                        <div class="container mt-sm-4 border rounded shadow">
                            <div class="row">';
                
                foreach ($fotos as $foto) {

                    $imagemBase64 = base64_encode($foto['imagem']);

                    echo '<div class="col-6 col-md-4 col-lg-3 mb-4 mt-4">
                            <img src="data:image/jpeg;base64,'.$imagemBase64.'" class="img-fluid mb-2 fixed-size-image" alt="">
                        </div>';
                }
                // Fecha o container da galeria
                echo '</div>
                    </div>';
            } else {
                echo "<div class='container d-flex align-items-center justify-content-center' style='height: 30vh;'>
                            <p>Sem imagens para esse TVG!</p>
                        </div>";
            }

    ?>



    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
</body>
</html>


