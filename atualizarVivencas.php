<?php

        require_once "conexao.php";
        
        $username = $_SESSION['username'];

            $queryUser = "SELECT id, permission FROM usuarios WHERE nome = :username";
            $stmtUser = $pdo->prepare($queryUser);
            $stmtUser->bindParam(":username", $username);
            $stmtUser->execute();

            $resultUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

            $querySessao = "SELECT nome, id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
            $stmtSessao = $pdo->prepare($querySessao);
            $stmtSessao->execute();
            $nomeSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);

        if(isset($nomeSessao['id'])){
            $idSessao = $nomeSessao['id'];
        }

        if ($resultUser) {
            $userId = $resultUser['id'];
            $queryProvas = "SELECT DISTINCT e.nome AS equipe_nome, p.nome AS prova_nome, p.id AS prova_id, ep.situacao AS situacao, ep.andamento AS andamento FROM equipes_provas AS ep
                        JOIN provas AS p ON  ep.id_provas = p.id
                        JOIN equipes AS e ON ep.id_equipes = e.id
                        JOIN gerenciamento_sessao AS gs ON e.id = gs.id_equipe
                        JOIN usuarios AS u ON gs.id_usuarios = u.id
                        JOIN sessoes AS ses ON ep.id_sessao = ses.id
                        WHERE ses.situacao = 'Pendente' AND ep.id_sessao = :id_sessao AND gs.id_sessoes = :id_sessao";
    
            $consulta = $pdo->prepare($queryProvas);
            $consulta->bindParam(":id_sessao", $idSessao);
            $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
    
    
            $equipesProcessadas = [];
    
            foreach ($data as $row) {
                if (!in_array($row['equipe_nome'], $equipesProcessadas)) {
                    echo "<div class='container-fluid'>";
                    echo "<div class='row'>
                                  <div class='col-md-10 mx-auto my-2 mt-4'> 
                                      <div class='border rounded shadow'>
                                          <h4 class='text-center mt-3 mb-0'>{$row['equipe_nome']}</h4>";
    
    
                    $equipesProcessadas[] = $row['equipe_nome'];
    
                   
    
                    foreach ($data as $prova) {
                        if ($prova['equipe_nome'] === $row['equipe_nome']) {
                            $icone_cor = '';
                            $icone = '';
                    
                            if ($prova['situacao'] === 'Pendente' && $prova['andamento'] === 'Aguardando') {
                                $icone_cor = 'text-danger';
                                $icone = 'fa-times';
                                $statusText = 'Aguardando';
                            } elseif ($prova['andamento'] === 'Execultando') {
                                $icone_cor = 'text-warning';
                                $icone = 'fa-hourglass';
                                $statusText = 'Pendente';
                            } elseif ($prova['situacao'] === 'Finalizado' || $prova['andamento'] === 'Finalizado') {
                                $icone_cor = 'text-success';
                                $icone = 'fa-check';
                                $statusText = 'Finalizado';
                            }
                    
                            $icone_animation_class = ($icone === 'fa-hourglass') ? 'fa-spin' : '';
                    
                            echo "<div class='card border-0'>
                                    <div class='card-body d-flex justify-content-between align-items-center p-3'>
                                        <h9 class='card-title m-0'>{$prova['prova_nome']}</h9>
                                        <div class='ml-auto'>
                                            <span class='badge {$icone_cor}'>" . 
                                                (($statusText === 'Pendente') ? '<span class="loading-text"></span>' : $statusText) . 
                                            "</span>
                                            <i class='fas {$icone} {$icone_animation_class} ml-2'></i>
                                        </div>
                                    </div>
                                </div>";
                        }
                    }
                    
                    echo "</div></div></div></div>";
                    echo "<div class='mt-4'></div>";
                }
            }
        }