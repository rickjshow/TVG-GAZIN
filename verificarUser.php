<?php
// Verifica se a requisição é uma requisição AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    include 'conexao.php';

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];

        $queryUser = "SELECT id, permission FROM usuarios WHERE nome = :username";
        $stmtUser = $pdo->prepare($queryUser);
        $stmtUser->bindParam(":username", $username);
        $stmtUser->execute();
        $resultUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

        $UserId = $resultUser['id'];

        $query = "SELECT situacao FROM usuarios WHERE id = :usuario_id";
        $consulta = $pdo->prepare($query);
        $consulta->bindParam(':usuario_id', $UserId);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($resultado['situacao'] === 'Inativo') {
            echo json_encode(['status' => 'inativo']);
        } else {
            echo json_encode(['status' => 'ativo']);
        }
    }
}
?>
