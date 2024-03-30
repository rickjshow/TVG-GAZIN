<?php 

include "conexao.php";

if(isset($_GET['idUsuario'])) {

    $id = $_GET['idUsuario'];

    $query = "SELECT nome FROM usuarios WHERE id = ?";
    $result = $pdo->prepare($query);
    $result->bindValue(1, $id);
    $result->execute();
    $nome = $result->fetchColumn();
    
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

            $user = $_SESSION['username'];

            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }
            
            $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

            $insert = "INSERT INTO log_facilitadores (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?, 'exclusão de usuário' , NOW() ,?, NULL)";
            $stmt = $pdo->prepare($insert);
            $stmt->bindValue(1, $user);
            $stmt->bindValue(2, $ip_user);
            $stmt->bindValue(3, $nome);
            $stmt->execute();

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