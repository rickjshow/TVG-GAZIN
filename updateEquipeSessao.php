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
    foreach ($sessoes as $sessao)

    $provas = $pdo->query("SELECT pro.nome AS prova_nome, ep.id AS provas_id FROM equipes_provas AS ep JOIN provas AS pro ON ep.id_provas = pro.id JOIN sessoes AS ses ON ep.id_sessao = ses.id WHERE id_sessao = {$sessao['sessao_id']} AND id_equipes = $id_equipe AND ses.situacao = 'Pendente'")->fetchAll(PDO::FETCH_ASSOC);
    $provas_ids = array(); 

    foreach ($provas as $prova) {
        $provas_ids[] = $prova['provas_id'];
    }

    $gs = $pdo->query("SELECT gs.id FROM gerenciamento_sessao AS gs JOIN sessoes AS ses ON gs.id_sessoes = ses.id WHERE id_equipe = $id_equipe AND ses.situacao = 'Pendente'")->fetchAll(PDO::FETCH_ASSOC);
    $gs_ids = array();

    foreach($gs as $gerent){
        $gs_ids[] = $gerent['id'];
    }

}

if (isset($_POST["confirmacao"]) || (isset($_POST["sessao"]) && isset($_POST["equipe"]) && isset($_POST["facilitador"]) && isset($_POST["participante"]) && isset($_POST["provas"]) && isset($_POST["id"]) && isset($_POST["id2"]))) {

    $sessao = $_POST["sessao"];
    $equipe = $_POST["equipe"];
    $facilitador = $_POST["facilitador"];
    $participantes = $_POST["participante"];
    $provas = $_POST["provas"];
    $id_array = $_POST['id'];
    $id2_array = $_POST['id2'];


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

        $sql = "UPDATE gerenciamento_sessao SET id_equipe = :id_equipe, id_usuarios = :id_usuarios, id_participantes = :id_participantes WHERE id = :id";
        $consulta = $pdo->prepare($sql);
        $consulta->bindParam(':id_equipe', $equipe_id);
        $consulta->bindParam(':id_usuarios', $facilitador_id);
        $consulta->bindParam(':id', $id);
        $consulta->bindParam(':id_participantes', $participantes_id);
        $consulta->execute();
    }
    
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

        
        $sql_provas = "UPDATE equipes_provas SET id_sessao = :id_sessoes, id_equipes = :id_equipe, id_provas = :id_provas WHERE id = :id2";
        $consulta_provas = $pdo->prepare($sql_provas);
        $consulta_provas->bindParam(':id_sessoes', $sessao_id);
        $consulta_provas->bindParam(':id_equipe', $equipe_id);
        $consulta_provas->bindParam(':id_provas', $provas_id);
        $consulta_provas->bindParam(':id2', $id2); 

        
        $consulta_provas->execute();
    }

    header("location: novaEdicao.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./fontawesome-free-6.5.1-web/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Editar equipe TVG</title>
</head>

<body>
    <div class="box1 mt-4 text-center">
        <h3 class="mt-4" style="font-size: 20px;">Gerenciamento Edição</h3>
    </div>
    <div class="container-fluid">
        <form action="updateEquipeSessao.php" method="post" id="meuFormulario">
            <?php foreach ($gs_ids as $id) : ?>
                <input type="hidden" name="id[]" value="<?= $id ?>">
            <?php endforeach; ?>

            <?php foreach ($provas_ids as $id2) : ?>
                <input type="hidden" name="id2[]" value="<?= $id2 ?>">
            <?php endforeach; ?>
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
            <div class="form-group">
                <label for="equipe">Equipe</label>
                <select id="equipe" name="equipe" class="form-control select2">
                    <?php
                    $query = "SELECT * FROM equipes";
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
                    $query1 = "SELECT * FROM usuarios";
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

            <div class="form-group">
                <label for="participante">Participantes</label>
                <select id="participante" class="select2 form-control" name="participante[]" multiple="multiple">
                    <?php

                    $query2 = "SELECT * FROM participantes";
                    $consulta2 = $pdo->prepare($query2);
                    $consulta2->execute();
                    $participantes2 = $consulta2->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($participantes2 as $participante) : ?>
                        <option value="<?= $participante['nome'] ?>" <?php echo (in_array($participante['nome'], array_column($participantes1, 'participante_nome'))) ? "selected" : ""; ?>>
                            <?= $participante['nome'] ?>
                        </option>
                    <?php endforeach;?>
                  
                </select>
            </div>

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

            <script>
                function validarFormulario() {
                    var sessao = document.getElementById('sessao').value;
                    var equipe = document.getElementById('equipe').value;
                    var facilitador = document.getElementById('facilitador').value;
                    var participantes = document.getElementById('participante').value;
                    var provas = document.getElementById('provas').value;

                    if (sessao === '' || equipe === '' || facilitador === '' || participantes === null || participantes.length === 0 || provas === null || provas.length === 0) {
                        alert('Favor preencher todos os campos!');
                    } else {
                        $('#exampleModal').modal('show');
                    }
                }
            </script>
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Sucessfull</h1>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Equipe alterada com sucesso
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="confirmacao" value="sim" class="btn btn-secondary" data-bs-dismiss="modal" onclick="enviarFormulario()">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>


        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
        <script>
            $(".select2").select2();
        </script>
</body>

</html>