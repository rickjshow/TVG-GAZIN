<?php

require_once("conexao.php");
require_once "permissao.php";
include "temporizador.php";
require_once "header.php";

verificarPermissao($permission);

$querySession = "SELECT id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
$ConsultaSession = $pdo->prepare($querySession);
$ConsultaSession->execute();
$idSessao = $ConsultaSession->fetch(PDO::FETCH_ASSOC);

if(isset($idSessao['id'])){
    $idSession = $idSessao['id'];
}else{
    $idSession = null;
}

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

    $queryProva = "SELECT COUNT(*) FROM equipes_provas WHERE id_sessao = :id_sessao AND id_provas = :id_provas";
    $consultaProva = $pdo->prepare( $queryProva);
    $consultaProva->bindValue(':id_sessao', $idSession);
    $consultaProva->bindValue(':id_provas', $id);
    $consultaProva->execute();

    $resultProvas = $consultaProva->fetchColumn();

    if($resultProvas == 0){

        $queryNomeOrig = "SELECT nome FROM provas WHERE id = :id";
        $consultaProva = $pdo->prepare($queryNomeOrig);
        $consultaProva->bindValue(':id', $id);
        $consultaProva->execute();
        $ProvaResult = $consultaProva->fetch(PDO::FETCH_ASSOC)['nome'];

        if($ProvaResult !== $nome){

            $queryNome = "SELECT nome FROM provas WHERE nome = :nome";
            $consultaNome = $pdo->prepare($queryNome);
            $consultaNome->bindValue(':nome', $nome);
            $consultaNome->execute();

        if($consultaNome->rowCount() > 0){
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Nome de prova já existente!');
            header("location: cadastro_provas.php");
            exit();
        }
    }

        $queryValoresAntigos = "SELECT p.nome AS nome, p.descricao AS descricao, p.pergunta AS pergunta, p.pontuacao_maxima AS pontuacao_maxima, tp.nome AS tipo_nome FROM provas AS p 
        JOIN tipo_provas AS tp ON p.tipo_provas_id = tp.id
        WHERE p.id = :id";
        $consultaValoresAntigos = $pdo->prepare($queryValoresAntigos);
        $consultaValoresAntigos->bindParam(':id', $id);
        $consultaValoresAntigos->execute();
        $valoresAntigos = $consultaValoresAntigos->fetch(PDO::FETCH_ASSOC);

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
            $consulta->execute();
        
    
            if ($consulta) {

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
    
                        $insertNome = "INSERT INTO log_vivencias (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição da prova " . $valoresAntigos['nome'] ." - nome', NOW(), ?, ?)";
                        $stmtNome = $pdo->prepare($insertNome);
                        $stmtNome->bindValue(1, $user);
                        $stmtNome->bindValue(2, $ip_user);
                        $stmtNome->bindValue(3, $valoresAntigos['nome']); 
                        $stmtNome->bindValue(4, $nome); 
                        $stmtNome->execute();
                    }

                    if ($valoresAntigos['descricao'] != $descricao) {
    
                        $insertNome = "INSERT INTO log_vivencias (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição da prova " . $nome ." - descrição', NOW(), ?, ?)";
                        $stmtNome = $pdo->prepare($insertNome);
                        $stmtNome->bindValue(1, $user);
                        $stmtNome->bindValue(2, $ip_user);
                        $stmtNome->bindValue(3, $valoresAntigos['descricao']); 
                        $stmtNome->bindValue(4, $descricao); 
                        $stmtNome->execute();
                    }

                    if ($valoresAntigos['pergunta'] != $pergunta) {
    
                        $insertNome = "INSERT INTO log_vivencias (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição da prova " . $nome ." - pergunta', NOW(), ?, ?)";
                        $stmtNome = $pdo->prepare($insertNome);
                        $stmtNome->bindValue(1, $user);
                        $stmtNome->bindValue(2, $ip_user);
                        $stmtNome->bindValue(3, $valoresAntigos['pergunta']); 
                        $stmtNome->bindValue(4, $pergunta); 
                        $stmtNome->execute();
                    }

                    if ($valoresAntigos['pontuacao_maxima'] != $pontos) {
    
                        $insertNome = "INSERT INTO log_vivencias (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição da prova " . $nome ." - ponto máximo', NOW(), ?, ?)";
                        $stmtNome = $pdo->prepare($insertNome);
                        $stmtNome->bindValue(1, $user);
                        $stmtNome->bindValue(2, $ip_user);
                        $stmtNome->bindValue(3, $valoresAntigos['pontuacao_maxima']); 
                        $stmtNome->bindValue(4, $pontos); 
                        $stmtNome->execute();
                    }
            
                    if ($valoresAntigos['tipo_nome'] != $tipo_provas) {
    
                        $insertDepartamento = "INSERT INTO log_vivencias (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?, ?, 'edição da prova " . $nome ." - tipo da prova', NOW(), ?, ?)";
                        $stmtDepartamento = $pdo->prepare($insertDepartamento);
                        $stmtDepartamento->bindValue(1, $user);
                        $stmtDepartamento->bindValue(2, $ip_user);
                        $stmtDepartamento->bindValue(3, $valoresAntigos['tipo_nome']); 
                        $stmtDepartamento->bindValue(4, $tipo_provas); 
                        $stmtDepartamento->execute();
                    } 

                session_start();
                $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Prova atualizada com sucesso!');
                header("location: cadastro_provas.php");
                exit();
            } else{
                session_start();
                $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro ao atualizar a Prova!');
                header("location: cadastro_provas.php");
                exit();
            }
        }else {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Não é possível atualizar provas que estejam vinculadas a um TVG pendente!');
            header("location: cadastro_provas.php");
            exit();
        }
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
    <style>
        @media (min-width: 768px){
            .col-md-6 {
                -ms-flex: 0 0 50%;
                flex: 0 0 50%;
                max-width: 95%;
            }
        }
    </style>
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