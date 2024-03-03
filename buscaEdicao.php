<?php

include "conexao.php";


    if(isset($_POST['buscar'])){
        if(isset($_POST['search']) && !empty($_POST['search'])){

        $nome = $_POST['search'];

        $query = "SELECT * FROM sessoes WHERE nome LIKE '%$nome%'";
        $consulta = $pdo->prepare($query);
        $consulta->execute();
        $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

        }elseif(isset($_POST['buscar']) && empty($_POST['search'])){
            $query = "SELECT * FROM sessoes ORDER BY data_criacao DESC LIMIT 8";
            $consulta = $pdo->prepare($query);
            $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    $queryG = "SELECT s.id AS sessao_id FROM gerenciamento_sessao AS gs
    JOIN sessoes AS s ON gs.id_sessoes = s.id";
    $consulta2 = $pdo->prepare($queryG);
    $consulta2->execute();
    $dados = $consulta2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($dados as $dado);

    ?>

    <?php
        if(isset($data) && !empty($data)){
       foreach ($data as $row) :
        ?>
        <tr>
            <th style="font-weight: normal;"><?php echo $row['nome']; ?></th>
            <th style="font-weight: normal;"><a><?php echo date('d/m/Y', strtotime($row['data_TVG'])); ?></a></th>
            <td>
                <a class="btn btn-<?php echo ($row['situacao'] == 'Pendente') ? 'danger' : 'success'; ?>" style="font-size: 12px;" id="btn">
                    <?php echo $row['situacao']; ?>
                </a>
            </td>
            <td>
                <?php

                    $queryG = "SELECT s.id AS sessao_id FROM gerenciamento_sessao AS gs
                    JOIN sessoes AS s ON gs.id_sessoes = s.id
                    WHERE s.id = :sessao_id";
                    $consulta2 = $pdo->prepare($queryG);
                    $consulta2->bindValue(':sessao_id', $row['id']);
                    $consulta2->execute();
                    $equipesCadastradas = $consulta2->fetch(PDO::FETCH_ASSOC);

                    $paginaRedirecionar = ($equipesCadastradas) ? 'updateGSessao.php' : 'gerenciamentoEdicao.php';
            
                    if($row['situacao'] != 'Finalizado')  : ?>
                        <a href="<?php echo $paginaRedirecionar; ?>?id=<?php echo $row['id']; ?>" style="font-size: 12px;" class="btn btn-success">Update</a>            
                    <?php elseif($row['situacao'] != 'Pendente') : ?>
                        <button class="btn btn-success" disabled style="font-size: 12px;">Update</button>                           
                    <?php  endif; ?>                                         
                </td>
            </tr>
        <?php endforeach; ?>
    <?php }else{
       echo "<tr>
            <tr><td colspan='4' class='text-center'>Sem resultados para essa consulta!</td></tr>;
        </tr>";
    } ?>