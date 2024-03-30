<?php

    include "conexao.php";
    include "header.php";
    require_once "permissao.php";
    include "temporizador.php";
    require_once "tipoDev.php";

    verificarTipo($type);

    verificarPermissao($permission);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alert.js"></script>
    <title>Document</title>
</head>
<body>

    <div class="container mt-4 mb-3">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold display-4 text-primary" style="font-size: 18px;">Log de Vivências</h3>
                <form method='post' action='logVivencias.php' class='mt-4'>
                    <div class='form-row align-items-center justify-content-center'>
                        <div class='col-auto'>
                        </div>
                        <div class='col-auto'>
                            <label class="mt-2" for='tvg'>Selecione o usuário:</label><br>
                            <select class='custom-select mr-sm-2' name='usuario' id='user' style='width: 200px;'>                                  
                                <?php
                                   $query = "SELECT * FROM usuarios ORDER BY data_entrada DESC";
                                   $querySessao = $pdo->prepare($query);
                                   $querySessao->execute();
                                   $SessaoName = $querySessao->fetchAll(PDO::FETCH_ASSOC);
                                   
                                   $nomeSelecionado = isset($_POST['tvg']) ? $_POST['tvg'] : '';
                                   
                                   echo "<option value='Todos os usuarios'";
                                   if ($nomeSelecionado == 'Todos os usuarios') {
                                       echo " selected";
                                   }
                                   echo ">Todos os usuarios</option>";
                                   
                                   foreach ($SessaoName as $row) {
                                       $selected = ($row['nome'] == $nomeSelecionado) ? 'selected' : '';
                                       echo "<option value='" . $row['nome'] . "' $selected>" . $row['nome'] . "</option>";
                                   }
                                            
                                ?>
                                        
                            </select>
                        </div>
                        <div class='col-auto'>
                            <label class="mt-2" for="datatvg">Data inicial:</label>
                            <input type="date" name="datainicial" class="form-control" style='width: 200px;'>
                        </div>
                        <div class='col-auto'>
                            <label class="mt-2" for="datatvg">Data final:</label>
                            <input type="date" name="datafinal" class="form-control" style='width: 200px;'>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="filtrar" class="btn btn-primary mt-4" style='width: 200px;'>Buscar Logs</button>
                    </div>
                </form>       
            </div>
    </div>
    <div class="container">
        <div class="container mt-sm-4 border rounded shadow mt-4">
        <div class="table-responsive-sm mt-4" style="font-size: 12px;">
            <table class="table table-sm table-hover table-striped">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Data</th>
                    <th>Ação</th>
                    <th>Valor Antigo</th>
                    <th>Valor Novo</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if(isset($_POST['filtrar'])){
                        $usuario = $_POST['usuario'];
                        $dataInicial = $_POST['datainicial'];
                        $dataFinal = $_POST['datafinal'];

                        $queryLog = "SELECT * FROM log_vivencias WHERE 1=1";
           
                        $bindings = array();
                                                
                        if (!empty($dataInicial) && !empty($dataFinal)) {
                            $queryLog .= " AND horario BETWEEN ? AND ?";
                            $bindings[] = $dataInicial;
                            $bindings[] = $dataFinal;
                        } elseif (!empty($dataInicial) && empty($dataFinal)) {
                            $queryLog .= " AND horario >= ?";
                            $bindings[] = $dataInicial;
                        } elseif (!empty($dataFinal) && empty($dataInicial)) {
                            $queryLog .= " AND horario <= ?";
                            $bindings[] = $dataFinal;
                        }
                                                
                        if ($usuario != 'Todos os usuarios') {
                            $queryLog .= " AND usuario = ?";
                            $bindings[] = $usuario;
                        }
                                                
                        $consulta3 = $pdo->prepare($queryLog);
                        $consulta3->execute($bindings);
                        
                        $resultado = $consulta3->fetchAll(PDO::FETCH_ASSOC);
                        
                        if(isset($resultado) && !empty($resultado)) {
                            foreach($resultado AS $row) {
                                echo "<tr>";
                                echo "<td style='font-weight: normal;'>{$row['usuario']}</td>";
                                echo "<td style='font-weight: normal;'>{$row['horario']}</td>";
                                echo "<td style='font-weight: normal;'>{$row['acao']}</td>";
                                if(isset($row['valor_antigo'])){
                                    echo "<td style='font-weight: normal;'>{$row['valor_antigo']}</td>";
                                }else{
                                    echo "<td style='font-weight: normal;'>Sem valor</td>";
                                }            
                                if(isset($row['valor_novo'])){
                                    echo "<td style='font-weight: normal;'>{$row['valor_novo']}</td>";
                                }else{
                                    echo "<td style='font-weight: normal;'>Sem valor</td>";
                                }   
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>Sem resultados para essa consulta!</td></tr>";
                        }
                    }
                ?>
            </tbody>
        </table>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</body>
</html>