<?php 
include "header.php";
include "importacao_participantes.php";
require_once "permissao.php";
include "temporizador.php";
include "conexao.php";

verificarPermissao($permission);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Planilha</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="alert.js"></script>
</head>
<body class="bg-light">

    <div class="container-fluid mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <h1 class="mb-4" style="font-size: 25px;">Sistema de Importação</h1>
                <div class="rounded p-4 bg-white shadow" >
                    <button class="btn btn-primary btn-lg btn-sm" data-toggle="modal" style="font-size: 15px;" data-target="#importModal">Importar Planilha</button>
                </div>
            </div>
        </div>
    </div>

    <?php

        $dataNascimento = '1965-12-13';

        $dataAtual = date('Y-m-d');

        $dataNascimentoObj = new DateTime($dataNascimento);
        $dataAtualObj = new DateTime($dataAtual);

        if ($dataAtualObj >= new DateTime(date('Y') . '-12-13')) {

            $dataNascimentoObj->modify('+1 year');

        }

        $idade = $dataNascimentoObj->diff($dataAtualObj)->y;
        echo "<div class='container mt-4'>";
            echo "<div class='row justify-content-center font-weight-bold'>";
                echo "Parabéns Gazin pelos seus " . $idade . " anos de idade!";
            echo "</div>";
        echo "</div>";

    ?>


<style>
    button#arquivo1 {
        margin-top: 85px;
        font-size: 15px;
    }
    button#arquivo2 {
        margin-top: 85px;
        font-size: 15px;
    }

    @media  only screen and (max-width: 499px) {
        a.btn.btn-success {
        font-size: 12px;
    }

    button#arquivo {
        font-size: 12px;
    }

    
}


</style>


<?php 
  require 'vendor/autoload.php';  // arquivo de autoload do Composer

  use PhpOffice\PhpSpreadsheet\Spreadsheet;
  use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
  
  $queryDepartamentos = "SELECT * FROM departamentos";
  $departamentos = $pdo->prepare($queryDepartamentos);
  $departamentos->execute();
  $result = $departamentos->fetchAll(PDO::FETCH_ASSOC);
  
  // Criei uma instância do Spreadsheet
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  

  // Adicionei os dados ao Spreadsheet
  $header = ['ID', 'Descrição']; 
  $sheet->fromArray([$header], null, 'A1');

  $sheet->fromArray($result, null, 'A2');
  
  // Salve o arquivo do Excel
  $writer = new Xlsx($spreadsheet);
  $writer->save('departamentos.xlsx');

    ?>

 
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Importar Planilha</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center">
                    <form action="importacao_participantes.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="planilha">Escolha a planilha:</label>
                            <input type="file" class="form-control-file" name="planilha" id="planilha" accept=".csv, .xlsx, .xls, .ods">
                        </div>
                        <button type="submit" id="arquivo" class="btn btn-primary" name="submit">Importar</button>
                        <a href="dados.ods" download="dados.ods" class="btn btn-success">Modelo</a>
                        <a href="departamentos.xlsx" download="departamentos.xlsx" class="btn btn-success">Departamento</a>
                    </form>
            </div>
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

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>