<?php

include "conexao.php";
include "header.php";
include "temporizador.php";

$queryEdicao = "SELECT id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
$resultadoEdicao = $pdo->prepare($queryEdicao);
$resultadoEdicao->execute();
$Edicao = $resultadoEdicao->fetchColumn();

$username = $_SESSION['username'];

$queryUser = "SELECT id, permission FROM usuarios WHERE nome = :username";
$stmtUser = $pdo->prepare($queryUser);
$stmtUser->bindParam(":username", $username);
$stmtUser->execute();

$resultUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alert.js"></script>
    <style>
        .fixed-size-image {
            width: 200px; 
            height: 200px; 
            object-fit: cover; 
        }
    </style>
    <title>Document</title> 
</head>
<body>


    <div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Galeria de Fotos</h3>
        </div>
    </div>

        <div class="container mt-4">
            <div class="box1 mt-4 text-center p-4 border rounded shadow">
                <?php 
                    if(isset($Edicao) && !empty($Edicao)){
                        echo "<button class='btn btn-primary mt-4' data-toggle='modal' style='font-size: 15px;' data-target='#importModal'>Adicionar Imagens</button>";           
                    }else{
                        echo"<p class='mt-4'>Sem TVG pendente no momento!</p>";  
                    }                           
                   
                    if($resultUser['permission'] == 'admin'){
                        echo "<form method='post' action='galeria.php' class='mt-4'>
                                <div class='form-row align-items-center justify-content-center'>
                                    <div class='col-auto'>
                                        <label class='sr-only' for='tvg'>Selecione o TVG:</label>
                                        <select class='custom-select mr-sm-2' name='tvg' id='tvg'>";                                  
                                            
                                            $query = "SELECT * FROM sessoes ORDER BY data_criacao DESC";
                                            $querySessao = $pdo->prepare($query);
                                            $querySessao->execute();
                                            $SessaoName = $querySessao->fetchAll(PDO::FETCH_ASSOC);

                                            $nomeSelecionado = isset($_POST['tvg']) ? $_POST['tvg'] : '';

                                            foreach ($SessaoName as $row) {
                                                $selected = ($row['nome'] == $nomeSelecionado) ? 'selected' : '';
                                                echo "<option value='" . $row['nome'] . "' $selected>
                                                    " . $row['nome'] . "
                                                </option>";
                                            }                                                                           
                                        
                                        echo "</select>
                                    </div>
                                    <div class='col-auto'>
                                        <button type='submit' name='filtrar' class='btn btn-primary'>Filtrar</button>
                                    </div>
                                </div>
                            </form>";
                    }elseif($resultUser['permission'] == 'limited'){
                        echo "<form method='post' action='galeria.php' class='mt-4'>
                            <div class='form-row align-items-center justify-content-center'>
                                <div class='col-auto'>
                                    <label class='sr-only' for='tvg'>Selecione o TVG:</label>
                                    <select class='custom-select mr-sm-2' name='tvg' id='tvg'>";

                                    // Verifica o valor enviado pelo formulário
                                    $tvgSelecionado = isset($_POST['tvg']) ? $_POST['tvg'] : '';

                                    // Adiciona as opções com base no valor selecionado
                                    echo "<option value='geral' " . ($tvgSelecionado == 'geral' ? 'selected' : '') . ">Geral</option>";
                                    echo "<option value='proprio' " . ($tvgSelecionado == 'proprio' ? 'selected' : '') . ">Meus anexos</option>";
                                    
                                    echo "</select>
                                </div>
                                <div class='col-auto'>
                                    <button type='submit' name='filtrar' class='btn btn-primary'>Filtrar</button>
                                </div>
                            </div>
                        </form>"; 
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
if(isset($_POST['filtrar'])){
    if(isset($_POST['tvg'])){
        $nome = $_POST['tvg'];

        if($nome == 'geral'){

            $queryImagem = "SELECT id, imagem FROM galeria WHERE id_sessoes = :id_sessoes";
            $stmtimagem = $pdo->prepare($queryImagem);
            $stmtimagem->bindParam(':id_sessoes', $Edicao);
            $stmtimagem->execute();

            if($stmtimagem->rowCount() <= 0){
                $mensagem = "<div class='container d-flex align-items-center justify-content-center' style='height: 30vh;'>
                                <p>Sem imagens para esse TVG!</p>
                            </div>";
            }
            
        }elseif($nome == 'proprio'){
            $queryImagem = "SELECT id, imagem FROM galeria WHERE id_sessoes = :id_sessoes AND id_usuarios = :id_user";
            $stmtimagem = $pdo->prepare($queryImagem);
            $stmtimagem->bindParam(':id_sessoes', $Edicao);
            $stmtimagem->bindParam(':id_user', $resultUser['id']);
            $stmtimagem->execute();

            if($stmtimagem->rowCount() <= 0){
                $mensagem = "<div class='container d-flex align-items-center justify-content-center' style='height: 30vh;'>
                                <p>Você ainda não anexou imagens nesse TVG!</p>
                            </div>";
            }

        }else{
            $queryId = "SELECT id FROM sessoes WHERE nome = :nome";
            $consultaId = $pdo->prepare($queryId);
            $consultaId->bindParam(':nome', $nome);
            $consultaId->execute();
            $id = $consultaId->fetchColumn();
    
            $queryImagem = "SELECT id, imagem FROM galeria WHERE id_sessoes = :id_sessoes";
            $stmtimagem = $pdo->prepare($queryImagem);
            $stmtimagem->bindParam(':id_sessoes', $id);
            $stmtimagem->execute();

            if($stmtimagem->rowCount() <= 0){
                $mensagem = "<div class='container d-flex align-items-center justify-content-center' style='height: 30vh;'>
                                <p>Sem imagens para esse TVG!</p>
                            </div>";
            }
        }
        
        if ($stmtimagem->rowCount() > 0) {
            // Recupera todas as imagens retornadas da consulta
            $fotos = $stmtimagem->fetchAll(PDO::FETCH_ASSOC);

            echo '<div class="container mt-4">
                    <div class="container mt-sm-4 border rounded shadow">
                        <div class="row">';

            foreach ($fotos as $foto) {
                $imagemBase64 = base64_encode($foto['imagem']);
                $imagemId = $foto['id'];

                if($resultUser['permission'] == 'limited' && $nome == 'geral'){
                    echo '<div class="col-6 col-md-4 col-lg-3 mb-4 mt-4 position-relative">
                            <img src="data:image/jpeg;base64,'.$imagemBase64.'" class="img-fluid mb-2 fixed-size-image" alt="">      
                        </div>';
                }elseif($resultUser['permission'] == 'limited' && $nome == 'proprio'){
                    echo '<div class="col-6 col-md-4 col-lg-3 mb-4 mt-4 position-relative">
                        <img src="data:image/jpeg;base64,'.$imagemBase64.'" class="img-fluid mb-2 fixed-size-image" alt="">
                        <a href="excluir_imagem.php?id='.$imagemId.'" class="position-absolute top-0 end-0 text-danger" style="margin-top: -13px; margin-right: 10px; font-size: 20px;">
                            <i class="fas fa-times-circle fa-xs"></i>
                        </a>
                      </div>';
                }elseif($resultUser['permission'] == 'admin'){
                    echo '<div class="col-6 col-md-4 col-lg-3 mb-4 mt-4 position-relative">
                    <img src="data:image/jpeg;base64,'.$imagemBase64.'" class="img-fluid mb-2 fixed-size-image" alt="">
                    <a href="excluir_imagem.php?id='.$imagemId.'" class="position-absolute top-0 end-0 text-danger" style="margin-top: -13px; margin-right: 5px; font-size: 20px;">
                        <i class="fas fa-times-circle fa-xs"></i>
                    </a>
                  </div>';
                }           
            }

                if ($resultUser['permission'] == 'admin') {
                    echo "</div>"; 

                    echo "<div class='container'>
                            <div class='row justify-content-end'>
                                <div class='col-auto'>
                                    <button id='downloadTodasAsImagens' class='btn btn-primary '>download</button>
                                     <div class='text-center mt-4'></div>
                                </div>
                            </div>
                        </div>";
                    echo '</div>';
                    echo '</div>';
                }
               
            echo "  <div class='text-center mt-4'></div>";
        } else {
            echo $mensagem;
        }
    }
}

?>
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

    <script>
        // Evento de clique no botão "Baixar todas as fotos"
        document.getElementById('downloadTodasAsImagens').addEventListener('click', function() {
            // Selecione todas as imagens exibidas
            var imagens = document.querySelectorAll('.fixed-size-image');
            
            // Verifique se há imagens
            if (imagens.length > 0) {
                // Itere sobre cada imagem
                imagens.forEach(function(imagem, index) {
                    // Converta a imagem em um URL de dados
                    var imgSrc = imagem.getAttribute('src');
                    var imagemBase64 = imgSrc.split(',')[1];
                    var blob = b64toBlob(imagemBase64, 'image/jpeg');
                    var url = window.URL.createObjectURL(blob);
                    
                    // Crie um link de download para a imagem
                    var link = document.createElement('a');
                    link.href = url;
                    link.download = 'imagem' + index + '.jpeg';
                    
                    // Adicione o link ao corpo do documento
                    document.body.appendChild(link);
                    
                    // Simule um clique no link para iniciar o download
                    link.click();
                    
                    // Remova o link do corpo do documento
                    document.body.removeChild(link);
                });
            } else {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'Não existem fotos para baixar!',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });

        // Função auxiliar para converter base64 em Blob
        function b64toBlob(b64Data, contentType, sliceSize) {
            contentType = contentType || '';
            sliceSize = sliceSize || 512;

            var byteCharacters = atob(b64Data);
            var byteArrays = [];

            for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
                var slice = byteCharacters.slice(offset, offset + sliceSize);

                var byteNumbers = new Array(slice.length);
                for (var i = 0; i < slice.length; i++) {
                    byteNumbers[i] = slice.charCodeAt(i);
                }

                var byteArray = new Uint8Array(byteNumbers);
                byteArrays.push(byteArray);
            }

            var blob = new Blob(byteArrays, { type: contentType });
            return blob;
        }
    </script>


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    
</body>
</html>