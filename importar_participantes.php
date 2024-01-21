<?php 
include "header.php";
include "importacao_participantes.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Planilha</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body class="bg-light">

    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <h1 class="mb-4" style="font-size: 25px;">Sistema de Importação</h1>
                <div class="rounded p-4 bg-white shadow" >
                    <button class="btn btn-primary btn-lg btn-sm" data-toggle="modal" style="font-size: 15px;" data-target="#importModal">Importar Planilha</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
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
                    <form action="importacao_participantes.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="planilha">Escolha a planilha:</label>
                            <input type="file" class="form-control-file" name="planilha" id="planilha" accept=".csv, .xlsx, .xls">
                        </div>
                        <button type="submit" name="arquivo" id="arquivo" class="btn btn-primary" name="submit">Importar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>
</html>

