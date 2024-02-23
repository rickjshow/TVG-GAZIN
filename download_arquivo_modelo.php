<?php 


if (isset($_POST['download_modelo'])) {

    $modeloPath = 'dados.ods'; 
    if (file_exists($modeloPath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($modeloPath) . '"');
        readfile($modeloPath);
        exit;
    } else {
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro ao baixar o modelo!');
        header('Location: importacao_participantes.php');
        exit();
    }
}



?>