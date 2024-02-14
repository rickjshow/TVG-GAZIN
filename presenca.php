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
                <h3 class='mt-4 font-weight-bold display-4 text-primary'  style='font-size: 18px;'>Lista de participantes Ausentes</h3>
                <h4 class='mt-4 text-center mx-auto' style=' color: black; max-width: 500px; font-size: 1.1em; padding:5px; border:solid #000 1px;'> Sessão Atual: <?php echo $nomeSession ?></h4>
            </div>
        <div class='container-fluid mt-4'>
            <div class="container mt-sm-4 border rounded shadow">
            <div class='table-responsive mt-4' style='font-size: 12px;'>
                <table class='table table-sm table-hover table-striped mt-4'>
                    <thead>
                        <tr>
                            <th>Facilitador</th>
                            <th>Participante</th>
                            <th>Equipe</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                                       
                        $query = "SELECT p.nome AS participante_nome, s.nome AS status_nome, e.nome AS equipe_nome, u.nome AS nome_facilitador FROM presenca AS pre
                            JOIN status AS s ON pre.id_status = s.id
                            JOIN participantes AS p ON pre.id_participantes = p.id
                            JOIN gerenciamento_sessao AS gs ON p.id = gs.id_participantes
                            JOIN usuarios AS u ON gs.id_usuarios = u.id
                            JOIN sessoes AS ses ON pre.id_sessao = ses.id
                            JOIN equipes AS e ON gs.id_equipe = e.id
                            WHERE s.nome = 'Ausente' AND ses.situacao = 'Pendente' AND gs.id_sessoes = :id_sessao";
                    
                        $consulta = $pdo->prepare($query);
                        $consulta->bindParam(":id_sessao", $nomeSessao['id']);
                        $consulta->execute();
                    
                        if ($consulta->rowCount() > 0) {
                            foreach ($consulta as $row) {
                                echo "<tr>";
                                echo "<th>{$row['nome_facilitador']}</th>";
                                echo "<th>{$row['participante_nome']}</th>";
                                echo "<th>{$row['equipe_nome']}</th>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center align-middle'>Não há participantes ausentes nesta sessão.</td></tr>";
                        }

                    ?>

                    </tbody>
                </table>
            </div>
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