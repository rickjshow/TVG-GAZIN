<?php 

include "conexao.php";

if(isset($_GET['idParticipante'])) {

    $id = $_GET['idParticipante'];
    
    $tabelas_filhas = array("gerenciamento_sessao", "presenca");
    
    $existe_vinculo = false;
    

    foreach ($tabelas_filhas as $tabela_filha) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM $tabela_filha WHERE id_participantes = :id");
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
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Não é possível excluir participantes que possuem vínculo!');
        header("location: participantes.php");
        exit();
    } else {
 
        $stmt_delete = $pdo->prepare("DELETE FROM participantes WHERE id = :id");
        $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete->execute();
        
        if ($stmt_delete->rowCount() > 0) {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Participante excluido com sucesso!');
            header("location: participantes.php");
            exit();
        } else {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro ao excluir participante!');
            header("location: participantes.php");
            exit();
        }
    }
}

?>