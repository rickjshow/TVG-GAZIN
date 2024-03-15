<?php
include "header.php";
require_once "conexao.php";
include "adicionarEquipeSessao.php";
require_once "permissao.php";
include "temporizador.php";

verificarPermissao($permission);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirmacao"])) {
    $confirmacao = $_POST["confirmacao"];

    if ($confirmacao === "sim") {
        $sqlsessao = "SELECT id FROM sessoes WHERE nome = :sessao";
        $consultasessao = $pdo->prepare($sqlsessao);
        $consultasessao->bindParam(':sessao', $_POST['sessao']);
        $consultasessao->execute();
        if ($consultasessao->rowCount() == 0) {
            echo "Erro: Sessão inválida.";
        } else {
            $sessao_id = $consultasessao->fetchColumn();
        }
        header("Location:gerenciamentoEdicao.php?continuar=1&idsessao=$sessao_id");
        exit();
    }
}

$sessoes = $pdo->query("SELECT nome FROM sessoes order by data_criacao desc limit 1 ")->fetchAll(PDO::FETCH_ASSOC);
if (isset($_GET['continuar']) && $_GET['continuar'] == 1) {
    $equipes = $pdo->query("SELECT e.* FROM equipes e WHERE not e.id in (select s.id_equipe from gerenciamento_sessao s where s.id_sessoes = {$_GET['idsessao']} )")->fetchAll(PDO::FETCH_ASSOC);
    $facilitadores = $pdo->query("SELECT u.* FROM usuarios u WHERE u.permission = 'limited' AND not u.id in (select s.id_usuarios from gerenciamento_sessao s where s.id_sessoes = {$_GET['idsessao']} )")->fetchAll(PDO::FETCH_ASSOC);
    $participantes = $pdo->query("SELECT p.* FROM participantes p WHERE not p.id in (select s.id_participantes from gerenciamento_sessao s where s.id_sessoes = {$_GET['idsessao']} ) ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $equipes = $pdo->query("SELECT * FROM equipes")->fetchAll(PDO::FETCH_ASSOC);
    $facilitadores = $pdo->query("SELECT * FROM usuarios WHERE permission = 'limited'")->fetchAll(PDO::FETCH_ASSOC);
    $participantes = $pdo->query("SELECT * FROM participantes")->fetchAll(PDO::FETCH_ASSOC);
}

$provas = $pdo->query("SELECT * FROM provas")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./fontawesome-free-6.5.1-web/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alertNao.js"></script>
    <link rel="stylesheet" href="cadastros.css">
    <title>Gerenciamento de Edição</title>
</head>

<body>

<div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold display-4 text-primary" style="font-size: 15px;">Gerenciamento Edição</h3>
        </div>
</div>

<div class="container mt-4">
    <div class="container-fluid border rounded p-4 shadow  col-md-10">
        <form action="gerenciamentoEdicao.php" method="post" id="meuFormulario">
            <div class="form-group">
                <label for="sessao">Sessão</label>
                <select id="sessao" name="sessao" class="form-control select2">
                    <?php foreach ($sessoes as $row) : ?>
                        <option value="<?= $row['nome'] ?>">
                            <?= $row['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="equipe">Equipe</label>
                <select id="equipe" name="equipe" class="form-control select2">
                    <?php foreach ($equipes as $row) : ?>
                        <option value="<?= $row['nome'] ?>">
                            <?= $row['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="facilitador">Facilitador</label>
                <select id="facilitador" name="facilitador" class="form-control select2">
                    <?php foreach ($facilitadores as $row) : ?>
                        <option value="<?= $row['nome'] ?>">
                            <?= $row['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="participante">Participantes</label>
                <select id="participante" class="select2 form-control" name="participante[]" multiple="multiple">
                    <?php foreach ($participantes as $row) : ?>
                        <option value="<?= $row['nome'] ?>">
                            <?= $row['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>


            <div class="form-group">
                <label for="provas">Provas</label>
                <select id="provas" class="select2 form-control" name="provas[]" multiple="multiple">
                    <?php foreach ($provas as $row) : ?>
                        <option value="<?= $row['nome'] ?>">
                            <?= $row['nome'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" class="btn btn-primary" style="font-size: 13px;" onclick="validarFormulario()">Adicionar</button>
</div>
<div class='text-center mt-4'></div>

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
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Atenção!</h1>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Equipe adicionada com sucesso! Deseja adicionar mais alguma equipe?
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="confirmacao" value="sim" class="btn btn-secondary" data-bs-dismiss="modal" onclick="enviarFormulario()">Sim</button>
                            <button type="button" id="btnNao" class="btn btn-primary">Não</button>
                        </div>
                        <script>
                            document.getElementById('btnNao').addEventListener('click', function () {
                                $('#exampleModal').modal('hide');
                                alertNao();
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
    <script>
        $(".select2").select2();
    </script>

</body>

</html>