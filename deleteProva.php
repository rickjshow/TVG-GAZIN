<?php 

include "conexao.php";

if(isset($_GET['idProva'])) {

    $id = $_GET['idProva'];
    
    $tabelas_filhas = array("equipes_provas", "pontuacao");
    
    $existe_vinculo = false;  

    foreach ($tabelas_filhas as $tabela_filha) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM $tabela_filha WHERE id_provas = :id");
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        $num_rows = $stmt_check->fetchColumn();
        
        if ($num_rows > 0) {
            $existe_vinculo = true;
            break;
        }
    }
    
    if ($existe_vinculo) {
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Não é possível excluir uma prova que possue vínculo!');
        header("location: cadastro_provas.php");
        exit();
    } else {
 
        $stmt_delete = $pdo->prepare("DELETE FROM provas WHERE id = :id");
        $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete->execute();
        
        if ($stmt_delete->rowCount() > 0) {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Prova excluida com sucesso!');
            header("location: cadastro_provas.php");
            exit();
        } else {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro ao excluir prova!');
            header("location: cadastro_provas.php");
            exit();
        }
    }
}

?>