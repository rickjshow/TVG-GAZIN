<?php
ob_start();
// Não precisa de session_start() aqui, pois já é chamado em "permissao.php"
require_once "permissao.php";
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="./fontawesome-free-6.5.1-web/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="index.js"></script>
    <title>Painel TVG</title>
    
</head>

<body>
    <?php
    if (isset($_SESSION["username"])) {

        require_once "conexao.php";

        $username = $_SESSION["username"];
        $permissao = obterPermissaoDoBancoDeDados($username);

        if (!$permissao) {
            $permissao = $_SESSION["permission"];
        }

        renderizarMenus($permissao);
    } else {
        header("location: index.php");
        exit();
    }

    function renderizarMenus($permissao)
    {
    ?>
        <div class='dashboard-app'>
        <header class='dashboard-toolbar  align-items-center justify-content-center'>
            <a class="menu-toggle"><i class="fas fa-bars"></i></a>
            <img src="gazin_logo.png" id="logo" alt="logogazin" class="mx-auto" style="width: 150px;">
        </header>
        <div class='dashboard-content'>
            <div class='container'>
                <div class="row">
                    <div class="col-lg-2">
                        <div class="dashboard-nav">

                        <header>

                                <a href="#!" class="menu-toggle"><i class="fas fa-bars"></i></a>
                              
                                <?php
                                    
                                    require_once "pdo.php";

                                    $user = $_SESSION['username'];

                                    $queryId = "SELECT id FROM usuarios WHERE nome = :user";
                                    $consultaId = $pdo->prepare($queryId);
                                    $consultaId->bindParam(':user', $user);
                                    $consultaId->execute();
                                    $Id = $consultaId->fetch(PDO::FETCH_ASSOC);

                                    $queryFotosUsuarios = "SELECT fotos FROM usuarios WHERE id = :id";
                                    $consultafoto = $pdo->prepare($queryFotosUsuarios);
                                    $consultafoto->bindParam(':id', $Id['id']);
                                    $consultafoto->execute();

                                    if ($consultafoto->rowCount() > 0) {
                                        $foto = $consultafoto->fetch(PDO::FETCH_ASSOC);
                                        $imagemBase64 = base64_encode($foto['fotos']);


                                    } else {
                                        echo "Nenhuma imagem encontrada.";
                                    }
                                    ?>
                                    <?php if (isset($imagemBase64) && !empty($imagemBase64)) : ?>
                                        <label for="fileInput" id="imgLabel">
                                            <img src="data:image/jpeg;base64,<?= $imagemBase64 ?>" id="fotoperfil" alt="Imagem do Banco de Dados" style="width: 50px; height: 50px; border-radius: 50%;">
                                        </label>
                                    <?php elseif (!isset($imagemBase64) || empty($imagemBase64)) : ?>
                                        <?php echo "Nenhuma imagem encontrada. Erro: "; ?>
                                    <?php endif; ?>

                                    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Escolher arquivo</h5>
                                                    <button type="button" onclick="fecharModal()" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                            <form action="updateimg.php" method="post" enctype="multipart/form-data">
                                                <div class="modal-body">
                                                    <label for="fileInput">Escolher arquivo</label>
                                                    <input type="file" id="fileInput" name="imgPerfil" accept="image/*" onchange="alterarFoto(this);">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="fecharModal()">Fechar</button>
                                                    <button type="submit" name="enviar" class="btn btn-primary">Enviar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    $(document).ready(function () {
                                        $('#imgLabel').on('click', function () {
                                            $('#myModal').modal('show');
                                        });
                                    });

                                    function fecharModal() {
                                        $('#myModal').modal('hide');
                                        $('#fileInput').val('');
                                    }

                                </script>


                                </header>   
                                
                                
                                <script>
                                    $(document).ready(function () {
                                        $('.dashboard-nav-item').on('click', function () {
                                            $('.dashboard-nav-item').removeClass('active');
                                            $(this).addClass('active');
                                        });
                                    });
                                </script>

                           
                            <nav class="dashboard-nav-list">
                                <a href="home.php" class="dashboard-nav-item active"><i class="fas fa-home"></i> Home</a>

                                <?php if ($permissao === "admin") : ?>
                                    <div class='dashboard-nav-dropdown'>
                                        <a href="#!" class="dashboard-nav-item dashboard-nav-dropdown-toggle"><i class="fas fa-file-upload"></i>Participantes</a>
                                        <div class='dashboard-nav-dropdown-menu'>
                                            <a href="participantes.php" class="dashboard-nav-dropdown-item">Cadastrar</a>
                                            <a href="importar_participantes.php" class="dashboard-nav-dropdown-item">Importar</a>
                                        </div>
                                        <a href="cadastro_provas.php" class="dashboard-nav-item"><i class="fa-solid fa-dice"></i>Vivencias TVG</a>
                                        <a href="novaEdicao.php" class="dashboard-nav-item"><i class="fa-solid fa-font-awesome"></i>TVG</a>
                                        <a href="acesso.php" class="dashboard-nav-item"><i class="fa-solid fa-user-plus"></i>Facilitadores</a>
                                        <a href="ranking.php" class="dashboard-nav-item"><i class="fa-solid fa-trophy"></i>Ranking</a>
                                        <div class='dashboard-nav-dropdown'>
                                            <a href="#!" class="dashboard-nav-item dashboard-nav-dropdown-toggle"><i class="fas fa-file-upload"></i>Relatorios</a>
                                            <div class='dashboard-nav-dropdown-menu'>
                                                <a href="#" class="dashboard-nav-dropdown-item">Presença</a>
                                                <a href="#" class="dashboard-nav-dropdown-item">Pontuação</a>
                                                <a href="#" class="dashboard-nav-dropdown-item">Dashboard</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <a href="presenca.php" class="dashboard-nav-item"><i class="fas fa-users"></i>Lista de chamada</a>
                                <a href="vivenciasPendentes.php" class="dashboard-nav-item"><i class="fa fa-id-badge" aria-hidden="true"></i>Vivências Pendentes</a>
                                <div class="nav-item-divider"></div>
                                <a href="logout.php" class="dashboard-nav-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
<?php
    }
?>
