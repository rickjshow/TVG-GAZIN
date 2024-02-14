<?php

require_once "header.php";
require_once "conexao.php";
require_once "adicionarPresenca.php";
include "temporizador.php";

$username = $_SESSION['username'];

$queryUser = "SELECT id, permission FROM usuarios WHERE nome = :username";
$stmtUser = $pdo->prepare($queryUser);
$stmtUser->bindParam(":username", $username);
$stmtUser->execute();

$resultUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

$UserId = $resultUser['id'];

$userType = $resultUser['permission'];

$querySessao = "SELECT nome, id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
$stmtSessao = $pdo->prepare($querySessao);
$stmtSessao->execute();
$nomeSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);

$queryRascunho = "SELECT COUNT(*) FROM rascunho_presenca AS rp
JOIN participantes AS part ON rp.id_participantes = part.id
JOIN sessoes AS s ON rp.id_sessao = s.id
JOIN usuarios AS u ON rp.id_user = u.id
WHERE s.id = :idSessao AND u.id = :idUser";
$consultaRascunho = $pdo->prepare($queryRascunho);
$consultaRascunho ->bindParam(":idSessao", $nomeSessao['id']);
$consultaRascunho ->bindParam(":idUser", $resultUser['id']);
$consultaRascunho->execute();
$numRascunho = $consultaRascunho->fetchColumn();

$queryChamada = "SELECT COUNT(*) FROM presenca AS p
JOIN participantes AS part ON p.id_participantes = part.id
JOIN sessoes AS s ON p.id_sessao = s.id
JOIN usuarios AS u ON p.id_user = u.id
WHERE s.id = :idSessao AND u.id = :idUser";
$consultaChamada = $pdo->prepare($queryChamada);
$consultaChamada ->bindParam(":idSessao", $nomeSessao['id']);
$consultaChamada ->bindParam(":idUser", $resultUser['id']);
$consultaChamada->execute();
$numChamada = $consultaChamada->fetchColumn();

?>


<!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="alert.js"></script>
        <script src="alertSucess.js"></script>
        <title>Presença</title>
    </head>

    <body>

        <script>
               function validarFormulario() {
                   var radios = document.querySelectorAll("input[type='radio']");
                   var radiosMarcados = document.querySelectorAll("input[type='radio']:checked");

                   if (radiosMarcados.length !== (radios.length / 2)) {
                       Swal.fire({
                           icon: 'error',
                           title: 'Erro!',
                           text: 'Por favor, marque a presença para todos os participantes.'
                       });
                       return false;
                   }

                   return true;
               }
        </script>

        <?php
            if(isset($nomeSessao['nome'])){
                $nomeSession = $nomeSessao['nome'];
            }
        ?>

        <div class='container mt-4'>
            <div class='box1 mt-4 text-center p-4 border rounded shadow'>
                <h3 class='mt-4 font-weight-bold display-4 text-primary'  style='font-size: 15px;'>Lista de chamada</h3>
                <h4 class='mt-4 text-center mx-auto' style=' color: black; max-width: 500px; font-size: 1.1em; padding:5px; border:solid #000 1px;'> Sessão Atual:<?php echo $nomeSession ?></h4>
            </div>

        <?php 

        if ($resultUser) {
            $userId = $resultUser['id'];
            $queryPart = "SELECT p.nome AS participante FROM participantes AS p
                JOIN gerenciamento_sessao AS gs ON p.id = gs.id_participantes
                JOIN usuarios AS u ON gs.id_usuarios = u.id
                JOIN sessoes AS s ON gs.id_sessoes = s.id
                WHERE u.id = :userId AND s.situacao = 'Pendente'";
            $stmtPart = $pdo->prepare($queryPart);
            $stmtPart->bindParam(":userId", $userId);
            $stmtPart->execute();
            $data = $stmtPart->fetchAll(PDO::FETCH_ASSOC);
        } else {
            echo "<tr><td colspan='3'>Usuário não encontrado.</td></tr>";
        }
        
        if($numRascunho == 0 && $numChamada == 0 && $data > 0){
            echo "<div class='container-fluid'>
                <form id='presencaForm' action='adicionarRascunho.php' method='post'>
                    <div class='table-responsive-sm mt-4'>
                        <table class='table table-sm table-hover table-striped' style='font-size: 13px;'>
                            <thead>
                                <tr>
                                    <th>Participante</th>
                                    <th>Presente</th>
                                    <th>Ausente</th>
                                </tr>
                            </thead>
                        <tbody>";

                                foreach ($data as $row) {
                                    echo "<tr>";
                                    echo "<td>{$row['participante']}</td>";
                                    echo "<td><input type='radio' name='presenca[{$row['participante']}]' value='Presente'></td>";
                                    echo "<td><input type='radio' name='presenca[{$row['participante']}]' value='Ausente'></td>";
                                    echo "</tr>";
                                }
            
                    echo "</tbody>
                    </table>
                    <input type='submit' class='btn btn-success mt-4' data-bs-toggle='modal' onclick='return validarFormulario()' name='adicionarRascunho' data-bs-target='#exampleModal' value='Gravar Rascunho'>
                </div>              
            </form>
        </div>";
        }elseif($data <= 0){
            echo "<div class='container d-flex align-items-center justify-content-center' style='height: 30vh;'>
                <p>Não existem participantes na sua equipe, entre em contato com o time de RH.</p>
            </div>";
        }elseif($numRascunho !== 0 && $numChamada == 0){          
                echo "<div class='container-fluid'>
                <form id='presencaForm' action='editarRascunho.php' method='post'>
                    <div class='table-responsive-sm mt-4'>
                        <table class='table table-sm table-hover table-striped' style='font-size: 13px;'>
                            <thead>
                                <tr>
                                    <th>Participante</th>
                                    <th>Presente</th>
                                    <th>Ausente</th>
                                </tr>
                            </thead>
                        <tbody>";

                            if ($resultUser) {
                                $userId = $resultUser['id'];
                                $queryPart = "SELECT p.nome AS participante FROM participantes AS p
                                    JOIN gerenciamento_sessao AS gs ON p.id = gs.id_participantes
                                    JOIN usuarios AS u ON gs.id_usuarios = u.id
                                    JOIN sessoes AS s ON gs.id_sessoes = s.id
                                    WHERE u.id = :userId AND s.situacao = 'Pendente'";
                                $stmtPart = $pdo->prepare($queryPart);
                                $stmtPart->bindParam(":userId", $userId);
                                $stmtPart->execute();
                                $data = $stmtPart->fetchAll(PDO::FETCH_ASSOC);

                                $queryRascunho = "SELECT p.nome AS participante, s.nome AS status FROM rascunho_presenca AS rp
                                JOIN status AS s ON rp.id_status = s.id
                                JOIN participantes AS p ON rp.id_participantes = p.id
                                JOIN gerenciamento_sessao AS gs ON p.id = gs.id_participantes
                                JOIN sessoes AS ses ON gs.id_sessoes = ses.id
                                JOIN usuarios AS u ON gs.id_usuarios = u.id
                                WHERE u.id = :userId AND ses.situacao = 'Pendente'";
                                $stmtRasc = $pdo->prepare($queryRascunho);
                                $stmtRasc->bindParam(":userId", $userId);
                                $stmtRasc->execute();
                                $dataRasc = $stmtRasc->fetchAll(PDO::FETCH_ASSOC);

                                foreach($dataRasc as $rasc){
                                    echo "<tr>";
                                    echo "<td>{$rasc['participante']}</td>";
                                    echo "<td><input type='radio' name='rascunho[{$rasc['participante']}]' value='Presente' " . ($rasc['status'] == 'Presente' ? 'checked' : '') . "></td>";
                                    echo "<td><input type='radio' name='rascunho[{$rasc['participante']}]' value='Ausente' " . ($rasc['status'] == 'Ausente' ? 'checked' : '') . "></td>";
                                    echo "</tr>";
                                }                           
                                                            
                            } else {
                                echo "<tr><td colspan='3'>Usuário não encontrado.</td></tr>";
                            }
                    echo "</tbody>
                    </table>
                    <input type='submit' class='btn btn-success mt-4' data-bs-toggle='modal' onclick='return validarFormulario()' name='editarRascunho' data-bs-target='#exampleModal' value='Editar Rascunho'>
                    <input type='submit' class='btn btn-danger mt-4' data-bs-toggle='modal' onclick='return validarFormulario()' name='confirmarPresenca' data-bs-target='#exampleModal' value='Confirmar Presença'>
                </div>              
            </form>
            </div>";
        }elseif($numRascunho == 0 && $numChamada > 0){
           echo "<div class='container d-flex align-items-center justify-content-center' style='height: 30vh;'>
                <p>A lista de chamada já foi finalizada.</p>
            </div>";
        }
                        
                if (isset($_SESSION['alertaSucesso'])) {
                    echo "<script>
                            alertaSucesso('{$_SESSION['alertaSucesso']['tipo']}', '{$_SESSION['alertaSucesso']['mensagem']}');
                        </script>";
                    unset($_SESSION['alertaSucesso']);
                }

                if (isset($_SESSION['alerta'])) {
                    echo "<script>
                            alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                            </script>";
                    unset($_SESSION['alerta']);
                    }
            ?>
            
            <script>
                function confirmarPresenca() {

                    var confirmacao = confirm("Tem certeza de que deseja concluir a presença?");

                    if (confirmacao) {

                        var form = document.getElementById('presencaForm');
                        
                        form.submit();
                    } else {

                        return false;
                    }
                }
            </script>


        <div id="login-expired-message" style="color: black;"></div>
        <script>
            resetTimer();
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</body>
</html>