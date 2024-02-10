<?php 

include "conexao.php";

if(isset($_GET['idUsuario'])) {

    $id = $_GET['idUsuario'];
    
    $existe_vinculo = false;
    
    $selectUser = "SELECT COUNT(*) FROM gerenciamento_sessao WHERE id_usuarios = :id";
    $stmt_check = $pdo->prepare($selectUser);
    $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_check->execute();
    $num_rows = $stmt_check->fetchColumn();
    
    if ($num_rows > 0) {
        $existe_vinculo = true;
    }

 
    if ($existe_vinculo) {
        session_start();
        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Não é possível excluir usuários que possuem vínculo!');
        header("location: acesso.php");
        exit();
    } else {
 
        $stmt_delete = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete->execute();
        
        if ($stmt_delete->rowCount() > 0) {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Usuário excluido com sucesso!');
            header("location: acesso.php");
            exit();
        } else {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro ao excluir usuário!');
            header("location: acesso.php");
            exit();
        }
    }
}

?>