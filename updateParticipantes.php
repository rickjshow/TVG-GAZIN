<?php
include("header.php");
include("conexao.php");
require_once "permissao.php";
include "temporizador.php";

verificarPermissao($permission);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $QueryParticipantes = "

    SELECT part.*, d.name AS departamento_nome FROM participantes AS part
    JOIN departamentos AS d ON part.id_departamentos = d.id
    WHERE part.id = :id

";

    $consulta = $pdo->prepare($QueryParticipantes);
    $consulta->bindParam(':id', $id, PDO::PARAM_INT);
    $consulta->execute();

    if (!$consulta) {
        die("Consulta falha");
    }

    $row = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Não foi possível recuperar os dados do banco de dados:<br> 
            Erro login: " . print_r($consulta->errorInfo(), true));
    }
}

if (isset($_POST['update_participantes'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $departamento_nome = $_POST['departamentos'];

    $sql_departamento = "SELECT id FROM departamentos WHERE name = :departamento_nome";
    $consulta_departamento = $pdo->prepare($sql_departamento);
    $consulta_departamento->bindParam(':departamento_nome', $departamento_nome);
    $consulta_departamento->execute();
    $resultado_departamento = $consulta_departamento->fetch(PDO::FETCH_ASSOC);

    $sqlParticipante = "
    UPDATE participantes
    SET
        nome = :nome,
        id_departamentos = :id_departamento
    WHERE id = :id 
";

    $consulta = $pdo->prepare($sqlParticipante);
    $consulta->bindValue(':nome', $nome);
    $consulta->bindParam(':id_departamento', $resultado_departamento['id']);
    $consulta->bindValue(':id', $id);
    $consulta->execute();

    header('Location: participantes.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Update Participantes</title>
</head>

<body>


<div class="container mt-4">
    <form action="updateParticipantes.php" method="post">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        
        <div class="form-group mt-4">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" class="form-control" value="<?= $row['nome'] ?>">
        </div>

        <div class="form-group">
            <label for="departamentos">Departamento:</label>
            <select name="departamentos" class="form-control">
                <?php
                $query = "SELECT * FROM departamentos";
                $consulta = $pdo->prepare($query);
                $consulta->execute();
                $departamentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

                foreach ($departamentos as $departamento) : ?>
                    <option value="<?= $departamento['name'] ?>" <?= ($row['departamento_nome'] == $departamento['name']) ? "selected" : "" ?>>
                        <?= $departamento['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success" name="update_participantes">ATUALIZAR</button>
    </form>
</div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>