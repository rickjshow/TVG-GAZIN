<?php
include("header.php");
include("conexao.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "
    SELECT u.*, d.name AS departamento_nome, t.tipo AS tipo_nome
    FROM usuarios AS u
    LEFT JOIN departamentos AS d ON u.id_departamentos = d.id
    JOIN tipo AS t ON u.id_tipo = t.id
    WHERE u.id = :id
    ";

    $consulta = $pdo->prepare($query);
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

if (isset($_POST['update_usuario'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $senha = $_POST['senha'];
    $situacao = $_POST['situacao'];
    $departamento_nome = $_POST['departamentos'];
    $tipo = $_POST["tipo"];

    $sql_departamento = "SELECT id FROM departamentos WHERE name = :departamento_nome";
    $consulta_departamento = $pdo->prepare($sql_departamento);
    $consulta_departamento->bindParam(':departamento_nome', $departamento_nome);
    $consulta_departamento->execute();
    $resultado_departamento = $consulta_departamento->fetch(PDO::FETCH_ASSOC);

    $sql_tipo = "SELECT id FROM tipo WHERE tipo = :tipo";
    $consulta_tipo = $pdo->prepare($sql_tipo);
    $consulta_tipo->bindParam(':tipo', $tipo);
    $consulta_tipo->execute();
    $resultado_tipo = $consulta_tipo->fetch(PDO::FETCH_ASSOC);

    $sqlUser = "
    UPDATE usuarios
    SET
        nome = :nome,
        senha = :senha,
        permission = 'limited',
        situacao = :situacao,
        id_departamentos = :id_departamento,
        id_tipo = :id_tipo
    WHERE id = :id 
    ";

    $consulta = $pdo->prepare($sqlUser);
    $consulta->bindValue(':nome', $nome);
    $consulta->bindValue(':senha', $senha);
    $consulta->bindValue(':situacao', $situacao);
    $consulta->bindParam(':id_departamento', $resultado_departamento['id']);
    $consulta->bindParam(':id_tipo', $resultado_tipo["id"]);
    $consulta->bindValue(':id', $id);
    $consulta->execute();

    header('Location: ./acesso.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> 
    <title>Document</title>
</head>
<body>

<div class="container-fluid mt-4">
    <form action="update.php" method="post">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <div class="form-group">
            <label>Usuario</label>
            <input type="text" name="nome" class="form-control" value="<?= $row['nome'] ?>">
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="text" name="senha" class="form-control" value="<?= $row['senha'] ?>">
        </div>
        <div class="form-group">
            <label for="situacao">Situação</label>
            <select name="situacao" class="form-control">
                <option value="Ativo" <?= ($row['situacao'] == 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                <option value="Inativo" <?= ($row['situacao'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
            </select>
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
        <div class="form-group">
            <label for="tipo">Tipo:</label>
            <select name="tipo" class="form-control">
                <?php
                $query = "SELECT * FROM tipo";
                $consulta = $pdo->prepare($query);
                $consulta->execute();
                $tipos = $consulta->fetchAll(PDO::FETCH_ASSOC);

                foreach ($tipos as $tipo) : ?>
                    <option value="<?= $tipo['tipo'] ?>" <?= ($row['tipo_nome'] == $tipo['tipo']) ? "selected" : "" ?>>
                        <?= $tipo['tipo'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="submit" class="btn btn-success" name="update_usuario" value="ATUALIZAR">
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>







    