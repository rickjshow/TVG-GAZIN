<?php

require_once ("conexao.php");
include "header.php";
require_once "permissao.php";
include "temporizador.php";

verificarPermissao($permission);

if(isset($_POST['Ativar']) && isset($_POST['idGS'])){

    $idGS = $_POST['idGS'];

    $querySessao = "SELECT u.id FROM usuarios AS u
    JOIN gerenciamento_sessao AS gs ON u.id = gs.id_usuarios
    JOIN sessoes AS s ON gs.id_sessoes = s.id
    WHERE s.id = :id_sessao";
    $consultaSessao = $pdo->prepare($querySessao);
    $consultaSessao->bindParam(':id_sessao', $idGS);
    $consultaSessao->execute();
    $resultado = $consultaSessao->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultado as $row) {
        $queryUpdateUser = "UPDATE usuarios SET situacao = 'Ativo' WHERE id = :id";
        $updateUser = $pdo->prepare($queryUpdateUser);
        $updateUser->bindParam(':id', $row['id']);
        $updateUser->execute();
    }
    
    session_start();
    if ($updateUser) {

        $query = "SELECT nome FROM sessoes WHERE id = ?";
            $result = $pdo->prepare($query);
            $result->bindValue(1, $idGS);
            $result->execute();
            $name = $result->fetchColumn();

            $user = $_SESSION['username'];

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }
            
            $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

            $querySessao = "SELECT data_TVG FROM sessoes WHERE id = ?";
            $resultado = $pdo->prepare($querySessao);
            $resultado->bindValue(1, $idGS);
            $resultado->execute();
            $data = $resultado->fetchColumn();

            $insert = "INSERT INTO log_sessoes (sessao, data_sessao, usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?,?,?, 'inicialização de sessão' , NOW() , NULL ,?)";
            $stmt = $pdo->prepare($insert);
            $stmt->bindValue(1, $name);
            $stmt->bindValue(2, $data);
            $stmt->bindValue(3, $user);
            $stmt->bindValue(4, $ip_user);
            $stmt->bindValue(5, $nome);
            $stmt->execute();

        $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'O TVG foi iniciado, boa sorte a todos!');
        header("location: home.php");
        exit();
    } else {
        header("location: novaEdicao.php");
        exit();
    }
}

if(isset($_GET['id'])){
    $id = $_GET['id'];
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
    <title>Edição gerenciamento sessão</title>
</head>

<body>
    
<div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold display-4 text-primary" style="font-size: 15px;">Equipes</h3>
            <?php 
            
                $queryVerificar = "SELECT COUNT(*) FROM usuarios AS u
                JOIN gerenciamento_sessao AS gs ON u.id = gs.id_usuarios
                WHERE gs.id_sessoes = ? AND u.situacao = 'Ativo'";
                $result = $pdo->prepare($queryVerificar);
                $result->bindValue(1, $id);
                $result->execute();
                $quantidade = $result->fetchColumn();

                if($quantidade <= 0) : ?>
                    <a class="btn btn-success mt-4" href="gerenciamentoEdicao.php?continuar=1&idsessao=<?php echo $id ?>">Adicionar Equipe</a>
                <?php endif;

            ?>
        </div>
</div>

    <div class="container-fluid text-center">
        <?php

        if (isset($_GET['id'])) {
            $idGS = $_GET['id'];
        }

        $queryGsession = "SELECT e.nome AS equipe_nome, e.id AS id_equipe FROM gerenciamento_sessao AS gs
                    JOIN equipes AS e ON gs.id_equipe = e.id
                    WHERE gs.id_sessoes = :idGS 
                    GROUP BY gs.id_equipe";
        $consultaid = $pdo->prepare($queryGsession);
        $consultaid->bindParam(':idGS', $idGS);
        $consultaid->execute();

        $dadosequipe = $consultaid->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dadosequipe as $row) {
         
            $equipe_nome = htmlspecialchars($row['equipe_nome']);
            $idGS = htmlspecialchars($idGS);

            echo "<div class='row'>
                        <div class='col-md-6 mx-auto'>
                            <a href='updateEquipeSessao.php?id={$row['id_equipe']}' style='text-decoration: none; color: black;'>
                                <div class='card mt-5 p-3 shadow-sm rounded' style='background-color: #f8f9fa;'>
                                    <div class='card-body text-center'>
                                        <h4 class='mb-3' style='color: #007bff;'>{$equipe_nome}</h4>
                                        <p class='font-weight-bold'>ID da Sessão: {$idGS}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>";
        }

        ?>
    </div>

<div class="container mt-4 mb-4">
    <div class="col-md-6 mx-auto border rounded shadow">

    <?php  
        $querySessao = "SELECT COUNT(*) FROM usuarios AS u
        JOIN gerenciamento_sessao AS gs ON u.id = gs.id_usuarios
        JOIN sessoes AS s ON gs.id_sessoes = s.id
        WHERE s.id = :id_sessao AND u.situacao = 'Ativo' AND u.permission = 'limited'";
        $consultaSessao = $pdo->prepare($querySessao);
        $consultaSessao->bindParam(':id_sessao', $idGS);
        $consultaSessao->execute();

        $resultado = $consultaSessao->fetch(PDO::FETCH_ASSOC);

        echo "<div class='d-flex justify-content-center align-items-center'>";
        if ($resultado['COUNT(*)'] == 0) {
            echo "<form action='updateGSessao.php' method='post'>
                    <input type='hidden' name='idGS' value='$idGS'>
                    <button type='submit' class='btn btn-success mr-2 mt-3' name='Ativar' style='font-size: 15px;'>Iniciar TVG</button>
                </form>";
        } else {
            echo '<p class="mt-4 p-2">TVG já foi iniciado!</p>';
        }
        
        echo "<button id='btnExcluirSessao' class='btn btn-danger mt-3' disabled style='font-size: 15px;'>Excluir Sessão</button>";
        echo "</div>";
    ?>

    <?php 

        if (isset($_GET['id'])) {
            $idGS = $_GET['id'];
        }

        $query = "SELECT COUNT(*) AS total_provas_nao_finalizadas
                FROM equipes_provas
                WHERE id_sessao = :id_sessao
                AND situacao <> 'Finalizado'";

        $consulta = $pdo->prepare($query);
        $consulta->bindValue(':id_sessao', $idGS);  
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        $totalProvasNaoFinalizadas = $resultado['total_provas_nao_finalizadas'];

        $queryRascunho = "SELECT COUNT(*) as total_rascunho FROM rascunho_presenca WHERE id_sessao = :id_sessao";
        $consultarRascunho = $pdo->prepare($queryRascunho);
        $consultarRascunho->bindValue(':id_sessao', $idGS);  
        $consultarRascunho->execute();
        $Rascunho = $consultarRascunho->fetch(PDO::FETCH_ASSOC);

        $resultadoRascunho = $Rascunho['total_rascunho'];

        $queryPresenca = "SELECT COUNT(*) as total_presenca FROM presenca WHERE id_sessao = :id_sessao";
        $consultaPresenca = $pdo->prepare($queryPresenca);
        $consultaPresenca->bindValue(':id_sessao', $idGS);  
        $consultaPresenca->execute();
        $Presenca = $consultaPresenca->fetch(PDO::FETCH_ASSOC);

        $resultadoPresenca = $Presenca['total_presenca']; 

        $queryUser  = "SELECT COUNT(*) AS total_user FROM usuarios AS u
        JOIN gerenciamento_sessao AS gs ON u.id = gs.id_usuarios
        WHERE gs.id_sessoes = :id_sessoes AND u.situacao = 'Ativo' AND permission = 'limited'";
        $consultaUser = $pdo->prepare($queryUser);
        $consultaUser->bindValue(':id_sessoes', $idGS);  
        $consultaUser->execute();
        $User = $consultaUser->fetch(PDO::FETCH_ASSOC);

        $usuario = $User['total_user'];       

    ?>

        <div class="container-fluid text-center p-4">
            <?php
                if($usuario <= 0){
                    echo '<p>O TVG ainda não foi iniciado.</p>';
                }elseif ($resultadoRascunho <= 0 && $resultadoPresenca <= 0){
                    echo '<p>A chamada ainda não foi feita, grave o rascunho e confirme a presença. Não é possível encerrar a sessão.</p>';
                }elseif($totalProvasNaoFinalizadas > 0){
                    echo '<p>Ainda há provas não finalizadas. Não é possível encerrar a sessão.</p>';
                }elseif($resultadoRascunho > 0 || $resultadoPresenca <= 0){
                    echo '<p>Ainda á rascunho de presença, finalize as listas de chamada. Não é possível encerrar a sessão.</p>';
                }elseif($totalProvasNaoFinalizadas <= 0 && $resultadoRascunho <= 0 && $resultadoPresenca > 0) {
                echo "<form action='finalizarSessao.php' method='post'>
                        <a href='finalizarSessao.php?id={$idGS}' type='submit' class='btn btn-secondary' data-dismiss='modal'>Finalizar Sessão</a>
                    </form>";
                }
            ?>      
        </div> 
    </div>
</div>
             
 <script>


    $(document).ready(function() {
        $("#btnExcluirSessao").prop("disabled", false);

        $("#btnExcluirSessao").click(function() {
            var idSessao = "<?php echo $idGS; ?>";

            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Esta ação encerrará a sessão. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, encerrar sessão!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'excluiSessao.php',
                        data: { idSessao: idSessao },
                        success: function(response) {
                            // Redirecionar para excluiSessao.php com o ID na URL
                            window.location.href = 'excluiSessao.php?idGS=' + idSessao;
                        },
                        error: function(error) {
                            console.error('Erro ao excluir sessão:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao encerrar a sessão. Por favor, tente novamente.'
                            });
                        }
                    });
                }
            });
        });
    });

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

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>