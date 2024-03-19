<?php 
    include "conexao.php";

    $numero = 1;

    $queryRanking = "SELECT e.nome AS equipe_nome, SUM(pontos.ponto_obtido) AS ponto_total,
                        (SELECT u.fotos FROM usuarios u
                         JOIN gerenciamento_sessao gs ON u.id = gs.id_usuarios
                         JOIN equipes AS e2 ON gs.id_equipe = e2.id
                         WHERE e2.id = e.id
                         ORDER BY gs.id DESC
                         LIMIT 1) AS fotos_facilitadores
                    FROM equipes AS e
                    JOIN pontuacao AS pontos ON pontos.id_equipes = e.id
                    JOIN sessoes AS ses ON pontos.id_sessoes = ses.id
                    WHERE ses.data_criacao = (
                        SELECT MAX(data_criacao) FROM sessoes
                    )
                    GROUP BY e.nome
                    ORDER BY ponto_total DESC";

    $consulta = $pdo->prepare($queryRanking);
    $consulta->execute();
    if($consulta->rowCount() > 0){
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

        foreach($data as $row) {
            echo "<tr>";
            echo "<th scope='row'>{$numero}</th>";
            echo "<td>{$row['equipe_nome']}</td>";
            echo "<td>{$row['ponto_total']}</td>";
            
            echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['fotos_facilitadores']) . "' alt='Foto do Facilitador' style='max-width: 50px; max-height: 50px; border-radius: 50%'></td>";
            
            echo "</tr>";
            $numero++;
        }
    }else{
        echo "<tr><td colspan='4' class='text-center'>Nenhuma sessão pendente ou prova finalizada no momento</td></tr>";
    } 
?>