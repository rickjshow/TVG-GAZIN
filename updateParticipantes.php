<?php
include("header.php");
require_once("conexao.php");
require_once "permissao.php";
include "temporizador.php";

verificarPermissao($permission);

$querySession = "SELECT id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
$ConsultaSession = $pdo->prepare($querySession);
$ConsultaSession->execute();
$idSessao = $ConsultaSession->fetch(PDO::FETCH_ASSOC);    

if(isset($idSessao['id'])){
    $idSession = $idSessao['id'];
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $QueryParticipantes = "

    SELECT part.*, d.name AS departamento_nome FROM participantes AS part
    JOIN departamentos AS d ON part.id_departamentos = d.id
    WHERE part.id = :id

";

    $consulta = $pdo->prepare($QueryParticipantes);
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

if (isset($_POST['update_participantes'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $departamento_nome = $_POST['departamentos'];

    $sql_departamento = "SELECT id FROM departamentos WHERE name = :departamento_nome";
    $consulta_departamento = $pdo->prepare($sql_departamento);
    $consulta_departamento->bindParam(':departamento_nome', $departamento_nome);
    $consulta_departamento->execute();
    $resultado_departamento = $consulta_departamento->fetch(PDO::FETCH_ASSOC);

    $queryPart = "SELECT COUNT(*) FROM gerenciamento_sessao WHERE id_sessoes = :id_sessoes AND id_participantes = :id_participantes";
    $consultaPart = $pdo->prepare($queryPart);
    $consultaPart->bindParam(':id_sessoes', $idSession);
    $consultaPart->bindParam(':id_participantes', $id);
    $consultaPart->execute();

    $resultPart =  $consultaPart->fetchColumn();

    if($resultPart == 0){
        $queryNomeOriginal = "SELECT nome FROM participantes WHERE id = :id";
        $consultaNome = $pdo->prepare($queryNomeOriginal);
        $consultaNome->bindParam(':id', $id);
        $consultaNome->execute();
        $nomeOriginal = $consultaNome->fetch(PDO::FETCH_ASSOC)['nome'];

        if($nomeOriginal !== $nome){
            $queryParticipante = "SELECT nome FROM participantes WHERE nome = :nome";
            $consultaParticipante = $pdo->prepare($queryParticipante);
            $consultaParticipante->bindParam(':nome', $nome);
            $consultaParticipante->execute();

            if($consultaParticipante->rowCount() > 0){
                session_start();
                $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Nome do participante já existente!');
                header("location: participantes.php");
                exit();
            }
        }

        $queryValoresAntigos = "SELECT p.nome AS nome, d.name AS departamento FROM participantes AS p 
        JOIN departamentos AS d ON p.id_departamentos = d.id
        WHERE p.id = :id";
        $consultaValoresAntigos = $pdo->prepare($queryValoresAntigos);
        $consultaValoresAntigos->bindParam(':id', $id);
        $consultaValoresAntigos->execute();
        $valoresAntigos = $consultaValoresAntigos->fetch(PDO::FETCH_ASSOC);
  
        $sqlParticipante = "UPDATE participantes SET nome = :nome, id_departamentos = :id_departamento WHERE id = :id";
        $consulta = $pdo->prepare($sqlParticipante);
        $consulta->bindValue(':nome', $nome);
        $consulta->bindParam(':id_departamento', $resultado_departamento['id']);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    
            if($consulta){
                
                $user = $_SESSION['username'];

                if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                } else {
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                }
                
                $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

                if ($valoresAntigos['nome'] != $nome) {

                    $insertNome = "INSERT INTO log_participantes (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição do(a) " . $valoresAntigos['nome'] . " - nome', NOW(), ?, ?)";
                    $stmtNome = $pdo->prepare($insertNome);
                    $stmtNome->bindValue(1, $user);
                    $stmtNome->bindValue(2, $ip_user);
                    $stmtNome->bindValue(3, $valoresAntigos['nome']); 
                    $stmtNome->bindValue(4, $nome); 
                    $stmtNome->execute();
                }
        
                if ($valoresAntigos['departamento'] != $departamento_nome) {

                    $insertDepartamento = "INSERT INTO log_participantes (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição do(a) " . $nome . " - departamento', NOW(), ?, ?)";
                    $stmtDepartamento = $pdo->prepare($insertDepartamento);
                    $stmtDepartamento->bindValue(1, $user);
                    $stmtDepartamento->bindValue(2, $ip_user);
                    $stmtDepartamento->bindValue(3, $valoresAntigos['departamento']); 
                    $stmtDepartamento->bindValue(4, $departamento_nome); 
                    $stmtDepartamento->execute();
                }

                $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Participante alterado com sucesso!');
                header("location: participantes.php");
                exit();
            }else{
                session_start();
                $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro ao atualizar participante!');
                header("location: participantes.php");
                exit();
            }  
    }else{
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Não é possível alterar participantes que estão participando do TVG!');
        header("location: participantes.php");
        exit();  
    }  
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>Update Participantes</title>
</head>

<body>

<div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold display-4 text-primary" style="font-size: 15px;">Atualizar Participantes</h3>
        </div>
</div>
   
<div class="container">
    <div class="container mt-4 border rounded p-4 shadow">
        <form action="updateParticipantes.php" method="post" class="mx-auto">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <div class="form-group mt-4">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" class="form-control" value="<?= $row['nome'] ?>">
            </div>

            <div class="form-group">
                <label for="departamentos">Departamento:</label>
                <select name="departamentos" class="form-control">
                    <?php
                    $query = "SELECT * FROM departamentos";
                    $consulta = $pdo->prepare($query);
                    $consulta->execute();
                    $departamentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($departamentos as $departamento) : ?>
                        <option value="<?= $departamento['name'] ?>" <?= ($row['departamento_nome'] == $departamento['name']) ? "selected" : "" ?>>
                            <?= $departamento['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success" style="font-size: 12px" name="update_participantes">Atualizar</button>
        </form>
        
        <div class="form-group">
            <button id="btnExcluirPart" class="btn btn-danger" style="font-size: 12px; margin-left:90px; margin-top:-59px">Excluir</button>
        </div>  
    </div>
</div>
        <?php
            if (isset($_SESSION['alerta'])) {
              echo "<script>
                      alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                   </script>";
              unset($_SESSION['alerta']);
            }
        ?>

 <script>

    $(document).ready(function() {
        $("#btnExcluirPart").prop("disabled", false);

        $("#btnExcluirPart").click(function() {
            var idParticipante = "<?php echo $id; ?>";

            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Esta ação irá excluir o Participante. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'deleteParticipantes.php',
                        data: { idSessao: idParticipante },
                        success: function(response) {
                            window.location.href = 'deleteParticipantes.php?idParticipante=' + idParticipante;
                        },
                        error: function(error) {
                            console.error('Erro ao excluir o Participante:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao excluir o Participante. Por favor, tente novamente.'
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>