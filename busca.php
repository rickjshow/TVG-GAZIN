<?php

include "conexao.php";

    if(isset($_POST['buscar'])){
        if(isset($_POST['search']) && !empty($_POST['search'])){
            
            $busca = $_POST['search'];
            $query = "SELECT p.*, d.name FROM participantes as p
            JOIN departamentos as d ON p.id_departamentos = d.id
            WHERE p.nome LIKE '%$busca%'";

            $consulta = $pdo->prepare($query);
            $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
        }elseif(isset($_POST['buscar']) && empty($_POST['search'])){

            $query = "SELECT p.*, d.name
            FROM participantes as p
            JOIN departamentos as d ON p.id_departamentos = d.id ORDER BY p.nome ASC LIMIT 8";
            
            $consulta = $pdo->prepare($query);
            $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
        }
    }

?>
    
    <?php if(isset($data) && !empty($data)): ?>
        <?php foreach ($data as $row) : ?>
        <tr>
            <th style="font-weight: normal;"><?php echo $row['nome']; ?></th>
            <th style="font-weight: normal;"><a><?php echo $row['name']; ?></a></th>
            <td><a href="updateParticipantes.php?id=<?php echo $row['id']; ?>"style="font-size: 12px;" class="btn btn-success" >Atualizar</a></td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <tr><td colspan='4' class='text-center'>Sem resultados para essa consulta!</td></tr>;
        </tr>
    <?php endif; ?> 