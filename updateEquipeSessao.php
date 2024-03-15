<?php

require_once "conexao.php";
include "header.php";
require_once "permissao.php";
include "temporizador.php";

verificarPermissao($permission);

if (isset($_GET['id'])) {
    $id_equipe = $_GET['id'];

    $equipes = $pdo->query("SELECT e.nome AS equipe_nome FROM gerenciamento_sessao AS gs JOIN equipes AS e ON gs.id_equipe = e.id JOIN sessoes AS ses ON gs.id_sessoes = ses.id WHERE gs.id_equipe = $id_equipe AND ses.situacao = 'Pendente'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($equipes as $equipe)

    $facilitadores = $pdo->query("SELECT u.nome AS nome_user, s.situacao AS ss FROM gerenciamento_sessao AS gs JOIN usuarios AS u ON gs.id_usuarios = u.id JOIN sessoes AS s ON gs.id_sessoes = s.id WHERE gs.id_equipe = $id_equipe AND s.situacao = 'Pendente'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($facilitadores as $usuario)

    $participantes1 = $pdo->query("SELECT p.id AS id_participantes, p.nome AS participante_nome FROM gerenciamento_sessao AS gs JOIN participantes AS p ON gs.id_participantes = p.id JOIN sessoes AS ses ON gs.id_sessoes = ses.id WHERE gs.id_equipe = $id_equipe AND ses.situacao = 'Pendente'")->fetchAll(PDO::FETCH_ASSOC);


    $sessoes = $pdo->query("SELECT s.nome AS nome_sessao, s.id AS sessao_id FROM gerenciamento_sessao AS gs JOIN sessoes AS s ON gs.id_sessoes = s.id WHERE gs.id_equipe = $id_equipe AND s.situacao ='Pendente'")->fetchAll(PDO::FETCH_ASSOC);
    $sesId = $sessoes[0]['sessao_id'];

    foreach ($sessoes as $sessao)

        $provas = $pdo->query("SELECT pro.nome AS prova_nome, ep.id AS provas_id FROM equipes_provas AS ep JOIN provas AS pro ON ep.id_provas = pro.id JOIN sessoes AS ses ON ep.id_sessao = ses.id WHERE id_sessao = {$sessao['sessao_id']} AND id_equipes = $id_equipe AND ses.situacao = 'Pendente'")->fetchAll(PDO::FETCH_ASSOC);
    $provas_ids = array();

    foreach ($provas as $prova) {
        $provas_ids[] = $prova['provas_id'];
    }

    $gs = $pdo->query("SELECT gs.id FROM gerenciamento_sessao AS gs JOIN sessoes AS ses ON gs.id_sessoes = ses.id WHERE id_equipe = $id_equipe AND ses.situacao = 'Pendente'")->fetchAll(PDO::FETCH_ASSOC);
    $gs_ids = array();

    foreach ($gs as $gerent) {
        $gs_ids[] = $gerent['id'];
    }
}


if (isset($_POST["confirmacao"])) {

    $sessao = $_POST["sessao"];
    $equipe = $_POST["equipe"];
    $facilitador = $_POST["facilitador"];
    $participantes = $_POST["participante"];
    $provas = $_POST["provas"];
    $id_array = $_POST['id'];
    $id2_array = $_POST['id2'];
    $id_equipe = $_POST['variavel'];


    $sqlsessao = "SELECT id FROM sessoes WHERE nome = :sessao";
    $consultasessao = $pdo->prepare($sqlsessao);
    $consultasessao->bindParam(':sessao', $sessao);
    $consultasessao->execute();

    if ($consultasessao->rowCount() == 0) {
        echo "Erro: Sessão inválida.";
        exit();
    }

    $sessao_id = $consultasessao->fetchColumn();

    $sqlequipe = "SELECT id FROM equipes WHERE nome = :equipe";
    $consultaequipe = $pdo->prepare($sqlequipe);
    $consultaequipe->bindParam(':equipe', $equipe);
    $consultaequipe->execute();

    if ($consultaequipe->rowCount() == 0) {
        echo "Erro: Equipe inválida.";
        exit();
    }

    $equipe_id = $consultaequipe->fetchColumn();

    $sqlfacilitador = "SELECT id FROM usuarios WHERE nome = :facilitador";
    $consultafacilitador = $pdo->prepare($sqlfacilitador);
    $consultafacilitador->bindParam(':facilitador', $facilitador);
    $consultafacilitador->execute();

    if ($consultafacilitador->rowCount() == 0) {
        echo "Erro: Facilitador inválido.";
        exit();
    }

    $facilitador_id = $consultafacilitador->fetchColumn();


    $sqlExcluiDadosSessao = "DELETE FROM gerenciamento_sessao WHERE id_sessoes = :id_sessoes AND id_equipe = :id_equipe";
    $consulta = $pdo->prepare($sqlExcluiDadosSessao);
    $consulta->bindParam(':id_sessoes', $sessao_id);
    $consulta->bindParam(':id_equipe', $id_equipe);
    $consulta->execute();


    foreach ($participantes as $key => $participanteNome) {
        $sqlparticipantes = "SELECT id FROM participantes WHERE nome = :participante";
        $conparticipantes = $pdo->prepare($sqlparticipantes);
        $conparticipantes->bindParam(':participante', $participanteNome);
        $conparticipantes->execute();

        if ($conparticipantes->rowCount() == 0) {
            echo "Erro: Participante inválido: $participanteNome. <br>";
            continue;
        }

        $participantes_id = $conparticipantes->fetchColumn();

        $id = $id_array[$key];

        $sql = "INSERT INTO gerenciamento_sessao (id_equipe, id_usuarios, id_participantes, id_sessoes) VALUES (:id_equipe,:id_usuarios,:id_participantes,:id_sessoes)";
        $consulta = $pdo->prepare($sql);
        $consulta->bindParam(':id_equipe', $equipe_id);
        $consulta->bindParam(':id_usuarios', $facilitador_id);
        $consulta->bindParam(':id_sessoes', $sessao_id);
        $consulta->bindParam(':id_participantes', $participantes_id);
        $consulta->execute();
    }

    $sqlExcluiDadosEqpSessao = "DELETE FROM equipes_provas WHERE id_sessao = :id_sessoes AND id_equipes = :id_equipes";
    $consulta = $pdo->prepare($sqlExcluiDadosEqpSessao);
    $consulta->bindParam(':id_sessoes', $sessao_id);
    $consulta->bindParam(':id_equipes', $id_equipe);
    $consulta->execute();

    foreach ($provas as $key => $provasNome) {
        $sqlprovas = "SELECT id FROM provas WHERE nome = :provas";
        $conprovas = $pdo->prepare($sqlprovas);
        $conprovas->bindParam(':provas', $provasNome);
        $conprovas->execute();

        if ($conprovas->rowCount() == 0) {
            echo "Erro: Prova inválida: $provasNome. <br>";
            continue;
        }

        $provas_id = $conprovas->fetchColumn();


        $id2 = $id2_array[$key];


        $sql_provas = "INSERT INTO equipes_provas (id_sessao, id_equipes, id_provas, situacao, andamento)  VALUES(:id_sessao, :id_equipes, :id_provas, 'Pendente', 'Aguardando')";
        $consulta_provas = $pdo->prepare($sql_provas);
        $consulta_provas->bindParam(':id_sessao', $sessao_id);
        $consulta_provas->bindParam(':id_equipes', $equipe_id);
        $consulta_provas->bindParam(':id_provas', $provas_id);


        $consulta_provas->execute();
    }

    header("location: novaEdicao.php");
    exit();
}

?>

<?php 

    $queryVerificar = "SELECT COUNT(*) FROM equipes_provas WHERE id_sessao = :id_sessao AND id_equipes = :id_equipes AND situacao = 'Finalizado' OR andamento = 'Execultando'";
    $consultaVerificar = $pdo->prepare($queryVerificar);
    $consultaVerificar->bindParam(':id_sessao', $sesId);
    $consultaVerificar->bindParam(':id_equipes', $id_equipe);
    $consultaVerificar->execute();
    $result = $consultaVerificar->fetchColumn();

    if($result > 0){
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'A equipe já possui provas em andamento, não é possível editar!');
        header("location: novaEdicao.php");
        exit;
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="./fontawesome-free-6.5.1-web/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Editar equipe TVG</title>
</head>

<body>


<div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold display-4 text-primary" style="font-size: 15px;">Gerenciamento Edição</h3>
        </div>
</div>
<div class="container mt-4">
    <div class="container-fluid border rounded p-4 shadow  col-md-10">
        <form action="updateEquipeSessao.php" method="post" id="meuFormulario">
        <input type="hidden" name="variavel" id="variavel" value="<?php echo $id_equipe ?>">
            <div class="form-group">
                <label for="sessao">Sessão</label>
                <select id="sessao" name="sessao" class="form-control select2">
                    <?php foreach ($sessoes as $sessao) : ?>
                        <option value="<?= $sessao['nome_sessao'] ?>">
                            <?= $sessao['nome_sessao'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php
            
                $queryCountUsuarios = "SELECT COUNT(*) FROM usuarios AS u
                JOIN gerenciamento_sessao AS gs ON u.id = gs.id_usuarios
                WHERE gs.id_equipe = :id_equipe AND gs.id_sessoes = :id_sessoes AND u.situacao = 'Ativo'";
                $CountUsuarios= $pdo->prepare($queryCountUsuarios);
                $CountUsuarios->bindParam(':id_equipe', $id_equipe);
                $CountUsuarios->bindParam(':id_sessoes', $sesId);
                $CountUsuarios->execute();
            
                $resultCount = $CountUsuarios->fetchColumn();
            
                if($resultCount <= 0) : ?>
                    <div class="form-group">
                        <label for="equipe">Equipe</label>
                        <select id="equipe" name="equipe" class="form-control select2">
                            <?php
                            $query = "SELECT e.* FROM equipes e where not exists (select 1 from gerenciamento_sessao gs where gs.id_sessoes = $sesId and gs.id_equipe <> $id_equipe and gs.id_equipe = e.id)";
                            $consulta = $pdo->prepare($query);
                            $consulta->execute();
                            $equipes2 = $consulta->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($equipes2 as $equipe1) : ?>
                                <option value="<?= $equipe1['nome'] ?>" <?= ($equipe['equipe_nome'] == $equipe1['nome']) ? "selected" : "" ?>>
                                    <?= $equipe1['nome'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="facilitador">Facilitador</label>
                        <select id="facilitador" name="facilitador" class="form-control select2">
                            <?php
                            $query1 = "SELECT u.* FROM usuarios u where not exists (select 1 from gerenciamento_sessao gs where gs.id_sessoes = $sesId and gs.id_equipe <> $id_equipe and gs.id_usuarios = u.id)";
                            $consulta1 = $pdo->prepare($query1);
                            $consulta1->execute();
                            $user2 = $consulta1->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($user2 as $user) : ?>
                                <option value="<?= $user['nome'] ?>" <?= ($usuario['nome_user'] == $user['nome']) ? "selected" : "" ?>>
                                    <?= $user['nome'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <?php else : ?>
                    <div class="form-group">
                        <label for="equipe">Equipe</label>
                        <select id="equipe" name="equipe" class="form-control select2" disabled>
                            <?php
                            $query = "SELECT e.* FROM equipes e where not exists (select 1 from gerenciamento_sessao gs where gs.id_sessoes = $sesId and gs.id_equipe <> $id_equipe and gs.id_equipe = e.id)";
                            $consulta = $pdo->prepare($query);
                            $consulta->execute();
                            $equipes2 = $consulta->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($equipes2 as $equipe1) : ?>
                                <option value="<?= $equipe1['nome'] ?>" <?= ($equipe['equipe_nome'] == $equipe1['nome']) ? "selected" : "" ?>>
                                    <?= $equipe1['nome'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="facilitador">Facilitador</label>
                        <select id="facilitador" name="facilitador" class="form-control select2" disabled>
                            <?php
                            $query1 = "SELECT u.* FROM usuarios u where not exists (select 1 from gerenciamento_sessao gs where gs.id_sessoes = $sesId and gs.id_equipe <> $id_equipe and gs.id_usuarios = u.id)";
                            $consulta1 = $pdo->prepare($query1);
                            $consulta1->execute();
                            $user2 = $consulta1->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($user2 as $user) : ?>
                                <option value="<?= $user['nome'] ?>" <?= ($usuario['nome_user'] == $user['nome']) ? "selected" : "" ?>>
                                    <?= $user['nome'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

            <?php endif; ?>
                
            <?php

                $queryUsuario = "SELECT u.* FROM usuarios AS u
                JOIN gerenciamento_sessao AS gs ON u.id = gs.id_usuarios
                WHERE gs.id_equipe = :id_equipe AND gs.id_sessoes = :id_sessoes";
                $consultaUsuario= $pdo->prepare($queryUsuario);
                $consultaUsuario->bindParam(':id_equipe', $id_equipe);
                $consultaUsuario->bindParam(':id_sessoes', $sesId);
                $consultaUsuario->execute();
                
                $user = $consultaUsuario->fetchAll(PDO::FETCH_ASSOC);

                foreach($user as $usuario){
                    $id_user = $usuario['id'];
                }

                $queryChamada = "SELECT COUNT(*) FROM presenca AS p
                JOIN sessoes AS s ON p.id_sessao = s.id
                JOIN gerenciamento_sessao AS gs ON s.id = gs.id_sessoes
                WHERE gs.id_sessoes = :id_sessoes AND gs.id_equipe = :id_equipe AND p.id_user = :id_user";
                $consultaChamada= $pdo->prepare($queryChamada);
                $consultaChamada->bindParam(':id_sessoes', $sesId);
                $consultaChamada->bindParam(':id_equipe', $id_equipe);
                $consultaChamada->bindParam(':id_user', $id_user);
                $consultaChamada->execute();
            
                $resultChamada = $consultaChamada->fetchColumn();
            
                $queryRascunho = "SELECT COUNT(*) FROM rascunho_presenca AS rp
                JOIN sessoes AS s ON rp.id_sessao = s.id
                JOIN gerenciamento_sessao AS gs ON s.id = gs.id_sessoes
                WHERE gs.id_sessoes = :id_sessoes AND gs.id_equipe = :id_equipe AND rp.id_user = :id_user";
                $consultaRascunho= $pdo->prepare($queryRascunho);
                $consultaRascunho->bindParam(':id_sessoes', $sesId);
                $consultaRascunho->bindParam(':id_equipe', $id_equipe);
                $consultaRascunho->bindParam(':id_user', $id_user);
                $consultaRascunho->execute();
                
                $resultRascunho = $consultaRascunho->fetchColumn();
            
                if($resultChamada <= 0 && $resultRascunho <= 0) : ?>

                    <div class="form-group">
                        <label for="participante">Participantes</label>
                        <select id="participante" class="select2 form-control" name="participante[]" multiple="multiple">
                            <?php


                            $query2 = "SELECT p.* FROM participantes p where not exists (select 1 from gerenciamento_sessao gs where gs.id_sessoes = $sesId and gs.id_equipe <> $id_equipe and gs.id_participantes = p.id)";
                            $consulta2 = $pdo->prepare($query2);
                            $consulta2->execute();
                            $participantes2 = $consulta2->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($participantes2 as $participante) : ?>
                                <option value="<?= $participante['nome'] ?>" <?php echo (in_array($participante['nome'], array_column($participantes1, 'participante_nome'))) ? "selected" : ""; ?>>
                                    <?= $participante['nome'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <?php else : ?>

                    <div class="form-group">
                        <label for="participante">Participantes</label>
                        <select id="participante" class="select2 form-control" name="participante[]" multiple="multiple" disabled>
                            <?php

                            $query2 = "SELECT p.* FROM participantes p where not exists (select 1 from gerenciamento_sessao gs where gs.id_sessoes = $sesId and gs.id_equipe <> $id_equipe and gs.id_participantes = p.id)";
                            $consulta2 = $pdo->prepare($query2);
                            $consulta2->execute();
                            $participantes2 = $consulta2->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($participantes2 as $participante) : ?>
                                <option value="<?= $participante['nome'] ?>" <?php echo (in_array($participante['nome'], array_column($participantes1, 'participante_nome'))) ? "selected" : ""; ?>>
                                    <?= $participante['nome'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <?php endif; ?>

            <div class="form-group">
                <label for="provas">Provas</label>
                <select id="provas" class="select2 form-control" name="provas[]" multiple="multiple">
                    <?php
                    $query3 = "SELECT * FROM provas";

                    $consulta3 = $pdo->prepare($query3);
                    $consulta3->execute();
                    $provas2 = $consulta3->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($provas2 as $prova2) : ?>
                        <option value="<?= $prova2['nome'] ?>" <?php echo (in_array($prova2['nome'], array_column($provas, 'prova_nome'))) ? "selected" : ""; ?>>
                            <?= $prova2['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" class="btn btn-primary" style="font-size: 13px;" onclick="validarFormulario()">Atualizar</button>
            <?php 
            
                $queryVerificar = "SELECT COUNT(*) FROM usuarios AS u
                JOIN gerenciamento_sessao AS gs ON u.id = gs.id_usuarios
                WHERE gs.id_sessoes = ? AND u.situacao = 'Ativo'";
                $result = $pdo->prepare($queryVerificar);
                $result->bindValue(1, $sesId);
                $result->execute();
                $quantidade = $result->fetchColumn();

                if($quantidade == 0) : ?>
                    <a class="btn btn-danger" style="font-size: 13px;" href="excluirEquipe.php?idEquipe=<?php echo $id_equipe; ?>&idSessao=<?php echo $sesId; ?>">Excluir Equipe</a>
                <?php endif;

            ?>
            
    </div>
        <script>
            function validarFormulario() {

                document.getElementById('equipe').removeAttribute('disabled');
                document.getElementById('facilitador').removeAttribute('disabled');
                document.getElementById('participante').removeAttribute('disabled');
                document.getElementById('provas').removeAttribute('disabled');

                var sessaoElement = document.getElementById('sessao');
                var equipeElement = document.getElementById('equipe');
                var facilitadorElement = document.getElementById('facilitador');
                var participantesElement = document.getElementById('participante');
                var provasElement = document.getElementById('provas');

                if (sessaoElement && equipeElement && facilitadorElement && participantesElement && provasElement) {

                    var sessao = sessaoElement.value;
                    var equipe = equipeElement.value;
                    var facilitador = facilitadorElement.value;
                    var participantes = participantesElement.value;
                    var provas = provasElement.value;

                    if (sessao === '' || equipe === '' || facilitador === '' || participantes === null || participantes.length === 0 || provas === null || provas.length === 0) {
                        alert('Favor preencher todos os campos!');
                    } else {
                        $('#exampleModal').modal('show');
                    }
                } else {
                    console.log('Alguns elementos do formulário não foram encontrados.');
                }
            }
        </script>

            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Sucesso</h1>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Alteração efetuada com sucesso!
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="confirmacao" value="sim" class="btn btn-secondary" data-bs-dismiss="modal" onclick="enviarFormulario()">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

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

                                        window.location.href = 'logout.php';
                                    } else {

                                        console.log('Usuário está ativo.');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error(error);
                                }
                            });
                        }
                        setInterval(verificarSituacaoUsuario, 10000);
                    });
            </script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
        <script>
            $(".select2").select2();
        </script>
</body>

</html>