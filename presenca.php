<?php
require_once "header.php";
require_once "conexao.php";
require_once "adicionarPresenca.php";
require_once "permissao.php";
include "temporizador.php";

verificarPermissao($permission);

$querySessao = "SELECT nome, id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
$stmtSessao = $pdo->prepare($querySessao);
$stmtSessao->execute();
$nomeSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);

if(isset($nomeSessao['nome'])){
    $nomeSession = $nomeSessao['nome'];
}else{
    $nomeSession = "Não existem sessões pendentes no momento";
}
  

?>


 <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Lista de ausentes</title>
    </head>
    <body>
    <div class='container mt-4'>
            <div class='box1 mt-4 text-center p-4 border rounded shadow'>
                <h3 class='mt-4 font-weight-bold display-4 text-primary'  style='font-size: 18px;'>Lista de presença</h3>
                <h4 class='mt-4 text-center mx-auto' style=' color: black; max-width: 500px; font-size: 1.1em; padding:5px; border:solid #000 1px;'> Sessão Atual: <?php echo $nomeSession ?></h4>
                <h3 class='display-4'  style='font-size: 16px; margin-top: 30px;'>Selecione o tipo de busca</h3>
                <div style="margin-top: 30px;">
                <form method="post" action="presenca.php">
                    <div class="form-row align-items-center justify-content-center">
                        <div class="col-auto">
                            <select class="custom-select mr-sm-2" name="tipo_busca" id="tipo_busca">
                                <option value="rascunho">Relação de rascunho</option>
                                <option value="listapart">Lista de participantes TVG</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="filtrar" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                </form>
                </div>
            </div>

            <?php 

                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    if(isset($_POST['tipo_busca'])){
                        $tipo = $_POST['tipo_busca'];
                        
                            if($tipo == 'rascunho') : ?>

                            <div class='container-fluid mt-4'>
                                        <div class="container mt-sm-4 border rounded shadow">
                                        <div class='table-responsive mt-4' style='font-size: 12px;'>
                                            <table class='table table-sm table-hover table-striped mt-4'>
                                                <thead>
                                                    <tr>
                                                        <th>Facilitador</th>
                                                        <th>Equipe</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                    
                                                <?php
                                                                
                                                    $query = "SELECT 'presenca' AS origem, s.nome AS status_nome, e.nome AS equipe_nome, u.nome AS nome_facilitador 
                                                    FROM presenca AS pre
                                                    JOIN status AS s ON pre.id_status = s.id                                                
                                                    JOIN sessoes AS ses ON pre.id_sessao = ses.id
                                                    JOIN usuarios AS u ON pre.id_user = u.id
                                                    JOIN equipes AS e ON pre.id_equipe = e.id
                                                    WHERE ses.situacao = 'Pendente' AND pre.id_sessao = :id_sessao
                                                    GROUP BY u.nome
                                                    UNION
                                                    SELECT 'rascunho_presenca' AS origem, s.nome AS status_nome, e.nome AS equipe_nome, u.nome AS nome_facilitador 
                                                    FROM rascunho_presenca AS rp
                                                    JOIN status AS s ON rp.id_status = s.id
                                                    JOIN sessoes AS ses ON rp.id_sessao = ses.id
                                                    JOIN usuarios AS u ON rp.id_user = u.id
                                                    JOIN equipes AS e ON rp.id_equipe = e.id
                                                    WHERE ses.situacao = 'Pendente' AND rp.id_sessao = :id_sessao
                                                    GROUP BY u.nome";
                                                
                                                    $consulta = $pdo->prepare($query);
                                                    $consulta->bindParam(":id_sessao", $nomeSessao['id']);
                                                    $consulta->execute();
                                                
                                                    if ($consulta->rowCount() > 0) {
                                                        foreach ($consulta as $row) {
                                                            echo "<tr>";
                                                            echo "<th style='font-weight: normal;'>{$row['nome_facilitador']}</th>";
                                                            echo "<th style='font-weight: normal;'>{$row['equipe_nome']}</th>";
                                                            if($row['origem'] == 'presenca'){
                                                                echo "<th style='font-weight: normal; color: green;'>Lista Final</th>";
                                                            }elseif($row['origem'] == 'rascunho_presenca'){
                                                                echo "<th style='font-weight: normal; color: red;'>Rascunho</th>"; 
                                                            }
                                                        
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='4' class='text-center align-middle'>Sem lista de chamada ou rascunho feita!</td></tr>";
                                                    }
                    
                                                ?>
                    
                                        </tbody>
                                </table>
                            </div>
                    
                        <?php elseif($tipo == 'listapart') : ?>

                <div class='container-fluid mt-4'>
                        <div class="container mt-sm-4 border rounded shadow">
                        <div class='table-responsive mt-4' style='font-size: 12px;'>
                            <table class='table table-sm table-hover table-striped mt-4'>
                                <thead>
                                    <tr>
                                        <th>Facilitador</th>
                                        <th>Participante</th>
                                        <th>Equipe</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                    
                                <?php 
                                
                                $query = "SELECT 'presenca' AS origem, p.nome AS participante_nome, s.nome AS status_nome, e.nome AS equipe_nome, u.nome AS nome_facilitador 
                                FROM presenca AS pre
                                JOIN status AS s ON pre.id_status = s.id
                                JOIN participantes AS p ON pre.id_participantes = p.id
                                JOIN gerenciamento_sessao AS gs ON p.id = gs.id_participantes
                                JOIN usuarios AS u ON gs.id_usuarios = u.id
                                JOIN sessoes AS ses ON pre.id_sessao = ses.id
                                JOIN equipes AS e ON gs.id_equipe = e.id
                                WHERE ses.situacao = 'Pendente' AND gs.id_sessoes = :id_sessao
                                UNION
                                SELECT 'rascunho_presenca' AS origem, p.nome AS participante_nome, s.nome AS status_nome, e.nome AS equipe_nome, u.nome AS nome_facilitador 
                                FROM rascunho_presenca AS rp
                                JOIN status AS s ON rp.id_status = s.id
                                JOIN participantes AS p ON rp.id_participantes = p.id
                                JOIN gerenciamento_sessao AS gs ON p.id = gs.id_participantes
                                JOIN usuarios AS u ON gs.id_usuarios = u.id
                                JOIN sessoes AS ses ON rp.id_sessao = ses.id
                                JOIN equipes AS e ON gs.id_equipe = e.id
                                WHERE ses.situacao = 'Pendente' AND gs.id_sessoes = :id_sessao";
                                
                                $consulta = $pdo->prepare($query);
                                $consulta->bindParam(":id_sessao", $nomeSessao['id']);
                                $consulta->execute();

                                if ($consulta->rowCount() > 0) {
                                    foreach ($consulta as $row) {
                                        echo "<tr>";
                                        echo "<th style='font-weight: normal;'>{$row['nome_facilitador']}</th>";
                                        echo "<th style='font-weight: normal;'>{$row['participante_nome']}</th>";
                                        echo "<th style='font-weight: normal;'>{$row['equipe_nome']}</th>";
                                        if($row['status_nome'] == 'Presente'){
                                            echo "<th style='font-weight: normal; color: green;'>Presente</th>";
                                        }elseif($row['status_nome'] == 'Ausente'){
                                            echo "<th style='font-weight: normal; color: red;'>Ausente</th>";
                                        }            
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center align-middle'>Sem lista de chamada ou rascunho feita!</td></tr>";
                                }
                                 
                                ?>
                    
                            </tbody>
                        </table>
                    </div>
                            <?php endif; 
                            
                        
                    }
                }

            ?>

        </body>
        </html>

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
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">