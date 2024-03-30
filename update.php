<?php
include("header.php");
require_once ("conexao.php");
include "temporizador.php";

    $querySession = "SELECT id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
    $ConsultaSession = $pdo->prepare($querySession);
    $ConsultaSession->execute();
    $idSessao = $ConsultaSession->fetch(PDO::FETCH_ASSOC);

    $username = $_SESSION['username'];

    $queryPermission = "SELECT t.tipo AS tipo FROM usuarios AS u
    JOIN tipo AS t ON u.id_tipo = t.id
    WHERE nome = ?";
    $result = $pdo->prepare($queryPermission);
    $result->bindValue(1, $username);
    $result->execute();
    $resultado = $result->fetchColumn();

    if(isset($idSessao['id'])){
        $idSession = $idSessao['id'];
    }

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "
    SELECT u.*, d.name AS departamento_nome, t.tipo AS tipo_nome
    FROM usuarios AS u
    LEFT JOIN departamentos AS d ON u.id_departamentos = d.id
    JOIN tipo AS t ON u.id_tipo = t.id
    WHERE u.id = :id
    ";

    $consulta = $pdo->prepare($query);
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

if (isset($_POST['update_usuario'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $situacao = $_POST['situacao'];
    $departamento_nome = $_POST['departamentos'];
    $tipo = $_POST["tipo"];

    if($tipo == "Desenvolvedor" || $tipo == "Gestor RH"){
        $permission = "admin";
    }else{
        $permission = "limited";
    }

    $sql_departamento = "SELECT id FROM departamentos WHERE name = :departamento_nome";
    $consulta_departamento = $pdo->prepare($sql_departamento);
    $consulta_departamento->bindParam(':departamento_nome', $departamento_nome);
    $consulta_departamento->execute();
    $resultado_departamento = $consulta_departamento->fetch(PDO::FETCH_ASSOC);

    $sql_tipo = "SELECT id FROM tipo WHERE tipo = :tipo";
    $consulta_tipo = $pdo->prepare($sql_tipo);
    $consulta_tipo->bindParam(':tipo', $tipo);
    $consulta_tipo->execute();
    $resultado_tipo = $consulta_tipo->fetch(PDO::FETCH_ASSOC);

    $queryUser = "SELECT COUNT(*) FROM gerenciamento_sessao WHERE id_usuarios = :id_usuarios AND id_sessoes = :id_sessoes";
    $consultaUser = $pdo->prepare($queryUser);
    $consultaUser->bindParam(':id_usuarios', $id);
    $consultaUser->bindParam(':id_sessoes', $idSession);
    $consultaUser->execute();

    $resultUser = $consultaUser->fetchColumn();

    if($resultUser == 0){

        $queryOriginalName = "SELECT nome FROM usuarios WHERE id = :id";
        $consultaOriginalName = $pdo->prepare($queryOriginalName);
        $consultaOriginalName->bindParam(':id', $id);
        $consultaOriginalName->execute();
        $originalName = $consultaOriginalName->fetch(PDO::FETCH_ASSOC)['nome'];

        if ($nome !== $originalName) {

            $queryNome = "SELECT nome FROM usuarios WHERE nome = :nome";
            $consultaNome = $pdo->prepare($queryNome);
            $consultaNome->bindParam(':nome', $nome);
            $consultaNome->execute();

            if($consultaNome->rowCount() > 0){
                session_start();
                $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Nome de usuário já existente');
                header("location: acesso.php");
                exit();
            }
        }

        $queryValoresAntigos = "SELECT u.nome AS nome, d.name AS departamento, u.situacao AS situacao, t.tipo AS tipo FROM usuarios AS u
        JOIN departamentos AS d ON u.id_departamentos = d.id
        JOIN tipo AS t ON u.id_tipo = t.id
        WHERE u.id = :id";
        $consultaValoresAntigos = $pdo->prepare($queryValoresAntigos);
        $consultaValoresAntigos->bindParam(':id', $id);
        $consultaValoresAntigos->execute();
        $valoresAntigos = $consultaValoresAntigos->fetch(PDO::FETCH_ASSOC);
        
        $sqlUser = "
        UPDATE usuarios
        SET
            nome = :nome,
            permission = :permission,
            situacao = :situacao,
            id_departamentos = :id_departamento,
            id_tipo = :id_tipo
        WHERE id = :id 
        ";
    
        $consulta = $pdo->prepare($sqlUser);
        $consulta->bindValue(':nome', $nome);
        $consulta->bindValue(':permission', $permission);
        $consulta->bindValue(':situacao', $situacao);
        $consulta->bindParam(':id_departamento', $resultado_departamento['id']);
        $consulta->bindParam(':id_tipo', $resultado_tipo["id"]);
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

                    $insertNome = "INSERT INTO log_facilitadores (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição do(a) " . $valoresAntigos['nome'] . " - nome', NOW(), ?, ?)";
                    $stmtNome = $pdo->prepare($insertNome);
                    $stmtNome->bindValue(1, $user);
                    $stmtNome->bindValue(2, $ip_user);
                    $stmtNome->bindValue(3, $valoresAntigos['nome']); 
                    $stmtNome->bindValue(4, $nome); 
                    $stmtNome->execute();
                }
        
                if ($valoresAntigos['departamento'] != $departamento_nome) {

                    $insertDepartamento = $sql = "INSERT INTO log_facilitadores (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição do(a) " . $nome . " - departamento', NOW(), ?, ?)";

                    $stmtDepartamento = $pdo->prepare($insertDepartamento);
                    $stmtDepartamento->bindValue(1, $user);
                    $stmtDepartamento->bindValue(2, $ip_user);
                    $stmtDepartamento->bindValue(3, $valoresAntigos['departamento']); 
                    $stmtDepartamento->bindValue(4, $departamento_nome); 
                    $stmtDepartamento->execute();
                }

                if ($valoresAntigos['situacao'] != $situacao) {

                    $insertNome = "INSERT INTO log_facilitadores (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição do(a) " . $nome . " - situacao', NOW(), ?, ?)";
                    $stmtNome = $pdo->prepare($insertNome);
                    $stmtNome->bindValue(1, $user);
                    $stmtNome->bindValue(2, $ip_user);
                    $stmtNome->bindValue(3, $valoresAntigos['situacao']); 
                    $stmtNome->bindValue(4, $situacao); 
                    $stmtNome->execute();
                }
        
                if ($valoresAntigos['tipo'] != $tipo) {

                    $insertDepartamento = "INSERT INTO log_facilitadores (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição do(a) " . $nome . " - tipo', NOW(), ?, ?)";
                    $stmtDepartamento = $pdo->prepare($insertDepartamento);
                    $stmtDepartamento->bindValue(1, $user);
                    $stmtDepartamento->bindValue(2, $ip_user);
                    $stmtDepartamento->bindValue(3, $valoresAntigos['tipo']); 
                    $stmtDepartamento->bindValue(4, $tipo); 
                    $stmtDepartamento->execute();
                }

            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Usuário Alterado com sucesso!');
            header("location: acesso.php");
            exit();
        }else{
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro ao alterar usuário!');
            header("location: acesso.php");
            exit();
        }
    }else{
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Não é possível alterar um usuário que está participando do TVG!');
        header("location: acesso.php");
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
    <title>Update Usuario</title>
</head>
<body>

<div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold display-4 text-primary" style="font-size: 15px;">Atualizar Usuario</h3>
        </div>
</div>




<div class="container mt-4">
    <div class="text-center mt-4"></div>
    <div class="container mt-4 border rounded p-4 shadow">
        <form action="update.php" method="post" class="mx-auto">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <div class="form-group">
                <label  class="mt-4" for="nome">Usuário:</label>
                <input type="text" name="nome" class="form-control" value="<?= $row['nome'] ?>">
            </div>
            <div class="form-group">
                <label for="situacao">Situação:</label>
                <select name="situacao" class="form-control">
                    <option value="Ativo" <?= ($row['situacao'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                    <option value="Inativo" <?= ($row['situacao'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
                </select>
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

            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select name="tipo" class="form-control">
                    <?php

                    if($resultado == "Desenvolvedor"){
                        $query = "SELECT * FROM tipo";
                    }else{
                        $query = "SELECT * FROM tipo WHERE tipo NOT IN('DESENVOLVEDOR')";
                    }

                    $consulta = $pdo->prepare($query);
                    $consulta->execute();
                    $tipos = $consulta->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($tipos as $tipo) : ?>
                        <option value="<?= $tipo['tipo'] ?>" <?= ($row['tipo_nome'] == $tipo['tipo']) ? "selected" : "" ?>>
                            <?= $tipo['tipo'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="submit" class="btn btn-success" style="font-size: 12px;" name="update_usuario" value="Atualizar">
        </form>
        <div class="form-group d-flex mt-4">
            <?php 

                if($_SESSION['username'] !== $row['nome']) : ?>

                    <button id="btnExcluiruser" class="btn btn-danger mr-2" style="font-size: 12px;">Excluir usuário</button>

                <?php endif;
            
            ?>
            <button id="btnResetar" class="btn btn-primary" style="font-size: 12px;">Resetar senha</button>
        </div>
    </div>
</div>
<div class="text-center mt-4"></div>
    <script>


    $(document).ready(function() {
        $("#btnExcluiruser").prop("disabled", false);

        $("#btnExcluiruser").click(function() {
            var id = "<?php echo $_GET['id']; ?>";

            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Esta ação irá excluir o usuário. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'deleteUsuario.php',
                        data: { idUser: id},
                        success: function(response) {
                            window.location.href = 'deleteUsuario.php?idUsuario=' + id;
                        },
                        error: function(error) {
                            console.error('Erro ao excluir o usuário:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao excluir o usuário. Por favor, tente novamente.'
                            });
                        }
                    });
                }
            });
        });
    });

    $(document).ready(function() {
        $("#btnResetar").prop("disabled", false);

        $("#btnResetar").click(function() {
            var id = "<?php echo $_GET['id']; ?>";

            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Esta ação irá resetar a senha do usuário. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'resetsenha.php',
                        data: { idUser: id},
                        success: function(response) {
                            window.location.href = 'resetsenha.php?idUsuario=' + id;
                        },
                        error: function(error) {
                            console.error('Erro ao resetar senha:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao resetar senha do usuário. Por favor, tente novamente.'
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