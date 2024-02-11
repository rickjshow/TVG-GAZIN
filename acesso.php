<?php
require_once "conexao.php";
require_once "permissao.php";
include "header.php";
include "adicionar.php";
include "temporizador.php";

verificarPermissao($permission);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alert.js"></script>
    <title>Painel TVG</title>
</head>

<body>
<div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Lista de Facilitadores</h3>
            <button class="btn btn-primary mt-4" data-toggle="modal" style="font-size: 15px;" data-target="#exampleModal">Cadastrar Usuarios</button>
        </div>
    </div>
    <div class="container-fluid ">    
        <div class="table-responsive-sm mt-4" style="font-size: 12px;">
            <table class="table table-sm table-hover table-striped">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Departamento</th>
                        <th>Editar</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $query = "SELECT u.*, d.name
                            FROM usuarios as u
                            JOIN departamentos as d ON u.id_departamentos = d.id
                            WHERE u.permission = 'limited'";
                            $consulta = $pdo->prepare($query);
                            $consulta->execute();
                            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($data as $row) : ?>
                        <tr>
                            <th><?php echo $row['nome']; ?></th>
                            <th><a><?php echo $row['name']; ?></a></th>
                            <td><a href="update.php?id=<?php echo $row['id']; ?>" style="font-size: 12px;" class="btn btn-success">Atualizar</a></td>
                            <td>
                                <a class="btn btn-<?php echo ($row['situacao'] == 'Ativo') ? 'success' : 'danger'; ?>" style="font-size: 12px;" id="btn">
                                    <?php echo $row['situacao']; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Adicionar Usuário</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="acesso.php" method="post">
                    <div class="form-group">
                        <label for="usuario">Usuário:</label>
                        <input type="text" name="nome" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha:</label>
                        <input type="password" name="senha" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="departamentos">Departamento:</label>
                        <select name="departamentos" class="form-control">
                            <?php
                            $query = "SELECT * FROM departamentos ORDER BY name";
                            $consulta = $pdo->prepare($query);
                            $consulta->execute();
                            $departamentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($departamentos as $row) : ?>
                                <option value="<?= $row['name'] ?>" <?= $row['name'] == "E-commerce" ? "selected" : "" ?>>
                                    <?= $row['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo">Tipo de usuário:</label>
                        <select name="tipo" class="form-control">
                            <?php
                            $query = "SELECT * FROM tipo WHERE tipo NOT IN('DESENVOLVEDOR')";
                            $consulta = $pdo->prepare($query);
                            $consulta->execute();
                            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($data as $row) : ?>
                                <option value="<?= $row['tipo'] ?>" <?= $row['tipo'] == "FACILITADORES" ? "selected" : "" ?>>
                                    <?= $row['tipo'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <input type="submit" class="btn btn-success" name="add_user" value="Adicionar">
            </div>
            </form>
            <?php
                    if (isset($_SESSION['alerta'])) {
                    echo "<script>
                            alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                            </script>";
                    unset($_SESSION['alerta']);
                    }
            ?>
        </div>
    </div>
</div>


    <div id="login-expired-message" style="color: black;"></div>
    <script>
        resetTimer();
    </script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>

</html>