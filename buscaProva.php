<?php

include "conexao.php";

    if(isset($_POST['buscar'])){
        if(isset($_POST['search']) && !empty($_POST['search'])){
            
            $nome = $_POST['search'];

            $query = "SELECT * FROM provas WHERE nome LIKE '%$nome%'";
            $consulta = $pdo->prepare($query);
            $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

            }elseif(isset($_POST['buscar']) && empty($_POST['search'])){

                $query = "SELECT * FROM provas ORDER BY nome ASC LIMIT 8";
                $consulta = $pdo->prepare($query);
                $consulta->execute();
                $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
            }
        }

?>

    <?php if(isset($data) && !empty($data)) : ?>
        <?php foreach ($data as $row) : ?>
            <tr>
                <th style="font-weight: normal;"><?php echo $row['nome']; ?></th>
                <td><a href="updateProva.php?id=<?php echo $row['id']; ?>" style="font-size: 12px;" class="btn btn-success">Atualizar</a></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <tr><td colspan='4' class='text-center'>Sem resultados para essa consulta</td></tr>;
        </tr>
    <?php endif; ?>