<?php

require_once "conexao.php";

    if (isset($_POST['buscar']) && isset($_POST['search']) && !empty($_POST['search'])) {

            $busca = $_POST['search'];

            $query = "SELECT u.*, d.name
            FROM usuarios as u
            JOIN departamentos as d ON u.id_departamentos = d.id
            WHERE u.permission = 'limited' AND u.nome LIKE '%$busca%'";
            $consulta = $pdo->prepare($query);
            $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }else{

        $query = "SELECT u.*, d.name
        FROM usuarios as u
        JOIN departamentos as d ON u.id_departamentos = d.id
        WHERE u.permission = 'limited' ORDER BY u.nome ASC LIMIT 8";
        $consulta = $pdo->prepare( $consulta);
        $consulta->execute();
        $data =  $consulta->fetchAll(PDO::FETCH_ASSOC);
        
    }

?>
    <?php if(isset($data) && !empty($data)) : ?>
        <?php foreach ($data as $row) : ?>
            <tr>
                <th><?php echo $row['nome']; ?></th>
                <th><a><?php echo $row['name']; ?></a></th>
                <td><a href="update.php?id=<?php echo $row['id']; ?>" style="font-size: 12px;" class="btn btn-success">Update</a></td>
                <td>
                    <a class="btn btn-<?php echo ($row['situacao'] == 'Ativo') ? 'success' : 'danger'; ?>" style="font-size: 12px;" id="btn">
                        <?php echo $row['situacao']; ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <tr><td colspan='4' class='text-center'>Sem resultados para essa consulta!</td></tr>;
        </tr>
    <?php endif; ?>

