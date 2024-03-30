<?php 

include "conexao.php";

if(isset($_GET['idProva'])) {

    $id = $_GET['idProva'];

    $query = "SELECT nome FROM provas WHERE id = ?";
    $result = $pdo->prepare($query);
    $result->bindValue(1, $id);
    $result->execute();
    $nome = $result->fetchColumn();
    
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

            $user = $_SESSION['username'];

                    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                    } else {
                        $ip_address = $_SERVER['REMOTE_ADDR'];
                    }
                    
                    $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

                    $queryUser = "SELECT id FROM usuarios WHERE nome = ?";
                    $result = $pdo->prepare($queryUser);
                    $result->bindValue(1, $user);
                    $result->execute();
                    $idUser = $result->fetchColumn();

                    $insert = "INSERT INTO log_vivencias (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?, 'exclusão de vivências' , NOW() ,?, NULL)";
                    $stmt = $pdo->prepare($insert);
                    $stmt->bindValue(1, $user);
                    $stmt->bindValue(2, $ip_user);
                    $stmt->bindValue(3, $nome);
                    $stmt->execute();

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