<?php
include("header.php");
require_once("conexao.php");
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <title>Update Participantes</title>
</head>

<body>


<div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold display-4 text-primary" style="font-size: 15px;">Atualizar Participantes</h3>
        </div>
</div>
   
<div class="container">
    <div class="container mt-4 border rounded p-4 shadow">
        <form action="updateParticipantes.php" method="post" class="mx-auto">
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

            <button type="submit" class="btn btn-success" style="font-size: 12px" name="update_participantes">Atualizar</button>
        </form>
        
        <div class="form-group">
            <button id="btnExcluirPart" class="btn btn-danger" style="font-size: 12px; margin-left:90px; margin-top:-59px">Excluir</button>
        </div>  
    </div>
</div>
        <?php
            if (isset($_SESSION['alerta'])) {
              echo "<script>
                      alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                   </script>";
              unset($_SESSION['alerta']);
            }
        ?>

 <script>


    $(document).ready(function() {
        $("#btnExcluirPart").prop("disabled", false);

        $("#btnExcluirPart").click(function() {
            var idParticipante = "<?php echo $id; ?>";

            Swal.fire({
                title: 'Você tem certeza?',
                text: 'Esta ação irá excluir o Participante. Deseja continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'deleteParticipantes.php',
                        data: { idSessao: idParticipante },
                        success: function(response) {
                            window.location.href = 'deleteParticipantes.php?idParticipante=' + idParticipante;
                        },
                        error: function(error) {
                            console.error('Erro ao excluir o Participante:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao excluir o Participante. Por favor, tente novamente.'
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