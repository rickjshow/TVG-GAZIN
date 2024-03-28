<?php
ob_start();

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
    <link rel="icon" href="iconegazin.png" type="image/x-icon">
    <script src="index.js"></script>
    <style>
        @media (min-width: 599px) {
                img#logo {
                    width: 175px !important; 
                    padding: -5px;
                }
            }
    </style>
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
           <img src="gazin_logo.png" id="logo" alt="logogazin" class="mx-auto" onclick="enviaHome()" style="width: 150px;">
        </header>
        <script>
                function enviaHome() {
                    window.location.href = 'home.php';
                }
        </script>


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

                                    $queryPermission = "SELECT t.tipo AS tipo, u.id AS id FROM usuarios AS u
                                    JOIN tipo AS t ON u.id_tipo = t.id
                                    WHERE nome = ?";
                                    $result = $pdo->prepare($queryPermission);
                                    $result->bindValue(1, $user);
                                    $result->execute();
                                    $resultado = $result->fetchAll(PDO::FETCH_ASSOC);

                                    foreach($resultado AS $row){
                                        $Id = $row['id'];
                                        $tipo = $row['tipo'];
                                    }

                                    $queryFotosUsuarios = "SELECT fotos FROM usuarios WHERE id = :id";
                                    $consultafoto = $pdo->prepare($queryFotosUsuarios);
                                    $consultafoto->bindParam(':id', $Id);
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
                                                <form id="mainForm" action="updateimg.php" method="post" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <label for="fileInput">Escolher arquivo</label>
                                                        <input type="file" id="fileInput" name="imgPerfil" accept="image/*" onchange="alterarFoto(this);">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" onclick="removerImagem()" class="btn btn-secondary">Remover Imagem</button>
                                                        <button type="submit" name="enviar" class="btn btn-primary">Enviar</button>
                                                    </div>
                                                </form>
                                                <form action="remove_img_usuario.php" method="post" id="removeForm">
                                                    <input type="submit" name="remove_img" id="removeImgBtn" style="display: none;">
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                        function fecharModal() {
                                            $('#myModal').modal('hide');
                                            $('#fileInput').val('');
                                        }

                                        function removerImagem() {
                                            // Muda a ação do formulário principal para remover_img_usuario.php
                                            document.getElementById('mainForm').action = 'remove_img_usuario.php';
                                            // Aciona o botão escondido quando o botão visível é clicado
                                            document.getElementById('removeImgBtn').click();
                                        }
                                    </script>


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
                                        <a href="acesso.php" class="dashboard-nav-item"><i class="fa-solid fa-user-plus"></i><?php if($tipo== "Desenvolvedor"){echo "Usuários";}else{echo "Facilitadores";} ?></a>
                                        <a href="ranking.php" class="dashboard-nav-item"><i class="fa-solid fa-trophy"></i>Ranking</a>
                                        <a href="presenca.php" class="dashboard-nav-item"><i class="fas fa-users"></i>Lista de chamada TVG</a>
                                        <a href="tarefas.php" class="dashboard-nav-item"><i class="fa-solid fa-pen-to-square"></i>Tarefas</a>
                                        <div class='dashboard-nav-dropdown'>
                                            <a href="#!" class="dashboard-nav-item dashboard-nav-dropdown-toggle"><i class="fas fa-file-upload"></i>Relatorios</a>
                                            <div class='dashboard-nav-dropdown-menu'>
                                                <a href="#" class="dashboard-nav-dropdown-item">Presença</a>
                                                <a href="#" class="dashboard-nav-dropdown-item">Pontuação</a>
                                                <a href="dashboard.php" class="dashboard-nav-dropdown-item">Dashboard</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if($permissao === "limited") : ?>
                                    <a href="rascunhoPresenca.php" class="dashboard-nav-item"><i class="fas fa-users"></i>Lista de chamada</a>
                                <?php endif; ?>
                                <a href="vivenciasPendentes.php" class="dashboard-nav-item"><i class="fa fa-id-badge" aria-hidden="true"></i>Vivências Pendentes</a>
                                <a href="galeria.php" class="dashboard-nav-item"><i class="fa-solid fa-image"></i></i>Galeria</a>
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