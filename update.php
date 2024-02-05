<?php
include("header.php");
require_once ("conexao.php");

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>Update Usuario</title>
</head>
<body>

<div class="text-center mt-4"></div>
<div class="container mt-4 border rounded p-4 shadow">
    <form action="update.php" method="post" class="mx-auto">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <h2 class="font-weight-bold  text-center">Atualizar Usuario</h2>
        <div class="form-group">
            <label  class="mt-4" for="nome">Usuário:</label>
            <input type="text" name="nome" class="form-control" value="<?= $row['nome'] ?>">
        </div>

        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="text" name="senha" class="form-control" value="<?= $row['senha'] ?>">
        </div>

        <div class="form-group">
            <label for="situacao">Situação:</label>
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

        <input type="submit" class="btn btn-success" style="font-size: 12px;" name="update_usuario" value="ATUALIZAR">
    </form>

    <div class="form-group">
         <button id="btnExcluiruser" class="btn btn-danger" style="font-size: 12px; margin-left:100px; margin-top:-59px">Excluir Usuário</button>
    </div>
</div>

    <script>


    $(document).ready(function() {
        $("#btnExcluiruser").prop("disabled", false);

        $("#btnExcluiruser").click(function() {
            var id = "<?php echo $_GET['id']; ?>";

            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Esta ação irá excluir o usuário. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'deleteUsuario.php',
                        data: { idUser: id},
                        success: function(response) {
                            window.location.href = 'deleteUsuario.php?idUsuario=' + id;
                        },
                        error: function(error) {
                            console.error('Erro ao excluir o usuário:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao excluir o usuário. Por favor, tente novamente.'
                            });
                        }
                    });
                }
            });
        });
    });


</script>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>  