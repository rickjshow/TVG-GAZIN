<?php 
    include "conexao.php";

    $numero = 1;

    $queryRanking = "SELECT e.nome AS equipe_nome, SUM(pontos.ponto_obtido) AS ponto_total 
                    FROM pontuacao AS pontos
                    JOIN equipes AS e ON pontos.id_equipes = e.id
                    JOIN sessoes AS ses ON pontos.id_sessoes = ses.id
                    WHERE ses.situacao = 'Pendente'
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
            echo "</tr>";
            $numero++;
        }
    }else{
        echo "<tr><td colspan='3' class='text-center'>Nenhuma sessão pendente no momento</td></tr>";
    }
    
?>