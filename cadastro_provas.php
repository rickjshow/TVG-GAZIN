<?php
include "header.php";
require_once "conexao.php";
require_once "permissao.php";
include "adicionarProva.php";
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
    <title>Cadastro de Provas</title>
</head>

<body>

        <div class="box1 mt-4 text-center">
            <h2 class="mt-4" style="font-size: 20px;">Cadastro Vivências TVG</h2>
            <button class="btn btn-primary mt-4" data-toggle="modal" style="font-size: 15px;" data-target="#exampleModal">Cadastro de Vivências</button>
        </div>
    <div class="container-fluid">
        <div class="table-responsive-sm mt-4">
            <table class="table table-sm table-hover table-striped" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>Vivencia</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM provas";
                    $consulta = $pdo->prepare($query);
                    $consulta->execute();
                    $data = $consulta->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php foreach ($data as $row) : ?>
                        <tr>
                            <th><?php echo $row['nome']; ?></th>
                            <td><a href="updateProva.php?id=<?php echo $row['id']; ?>" style="font-size: 12px;" class="btn btn-success">Atualizar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Adicionar Vivencia</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="adicionarProva.php" method="post">
                            <div class="form-group">
                                <label for="prova">Vivência:</label>
                                <input type="text" name="nome_prova" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Descriçao:</label>
                                <textarea class="form-control" name="descricao_prova" id="exampleFormControlTextarea1" cols="30" rows="10"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Perguntas:</label>
                                <textarea class="form-control" name="pergunta_prova" id="exampleFormControlTextarea1" cols="30" rows="10"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="pontos">Pontuação Maxima prova:</label>
                                <input type="number" name="pontos" class="form-control">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <input type="submit" class="btn btn-success" name="add_prova" value="Adicionar">
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