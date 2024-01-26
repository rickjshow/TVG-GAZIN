<?php

    include "conexao.php";
    include "header.php";
    require_once "permissao.php";
    include "temporizador.php";

    verificarPermissao($permission);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edição gerenciamento sessão</title>
</head>
<body>
    <div class="container-fluid text-center">
        <div class="box1 mt-4 text-center">
            <h1 class='mt-4' style='font-size: 40px;'>Equipes</h1>
        </div>
    </div>

    <?php

        if(isset($_GET['id'])){
            $idGS = $_GET['id'];
        }else{
            echo "<script>alert('ID não encontrado!');</script>";
        }

        $queryGsession = "SELECT e.nome AS equipe_nome, gs.id AS id_gs FROM gerenciamento_sessao AS gs
        JOIN equipes AS e ON gs.id_equipe = e.id
        WHERE gs.id_sessoes = :idGS ";
        $consultaid = $pdo->prepare($queryGsession);
        $consultaid->bindParam(':idGS', $idGS);
        $consultaid->execute();

        $dadosequipe = $consultaid->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dadosequipe as $row) {
            echo "<div class='row'>
                      <div class='col-sm-6 mb-3 mb-sm-0 mx-auto'>
                        <a href='updateEquipeSessao.php?id={$row['id_gs']}' style='text-decoration: none; color: black;'>
                              <div class='card mt-5'>
                                   <div class='card-body text-center'>
                                        <h4>{$row['equipe_nome']}</h4>
                                   </div>
                               </div>
                           </a>
                       </div>
                   </div>";
        }


    ?>


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>