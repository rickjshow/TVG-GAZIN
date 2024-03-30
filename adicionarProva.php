<?php
require_once "conexao.php";
if (isset($_POST["add_prova"])) {
    if (empty($_POST["nome_prova"]) || empty($_POST["descricao_prova"]) || empty($_POST['pergunta_prova']) || empty($_POST['tipo_prova']) || empty($_POST['pontos'])) {
        echo "<script>alert('Favor inserir todos os dados!'); window.location.href='cadastro_provas.php';</script>";
        exit();
    } else {
        $nome = $_POST["nome_prova"];
        $descricao = $_POST["descricao_prova"];
        $pergunta = $_POST["pergunta_prova"];
        $pontos = $_POST['pontos'];
        $tipo_prova = $_POST['tipo_prova'];

        $queryTipo = "SELECT id FROM tipo_provas WHERE nome = :nome";
        $consultaTipo = $pdo->prepare($queryTipo);
        $consultaTipo->bindParam(':nome', $tipo_prova);
        $consultaTipo->execute();
        $resultado_tipo =  $consultaTipo->fetch(PDO::FETCH_ASSOC);

        $sql = "INSERT INTO provas(nome, descricao, pergunta, pontuacao_maxima, tipo_provas_id) VALUES(:nome, :descricao, :pergunta, :pontos, :tipo_prova)";

        $consulta = $pdo->prepare($sql);
        $consulta->bindParam(':nome', $nome);
        $consulta->bindParam(':descricao', $descricao);
        $consulta->bindParam(':pergunta', $pergunta);
        $consulta->bindParam(':pontos', $pontos);
        $consulta->bindParam(':tipo_prova', $resultado_tipo['id'], PDO::PARAM_INT);
        if ($consulta->execute()) {

            $user = $_SESSION['username'];

                    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                    } else {
                        $ip_address = $_SERVER['REMOTE_ADDR'];
                    }
                    
                    $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

                    $insert = "INSERT INTO log_vivencias (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?, 'adição de vivências' , NOW() , NULL ,?)";
                    $stmt = $pdo->prepare($insert);
                    $stmt->bindValue(1, $user);
                    $stmt->bindValue(2, $ip_user);
                    $stmt->bindValue(3, $nome);
                    $stmt->execute();

            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Cadastrado com sucesso!');
            header("location: cadastro_provas.php");
            exit();
        } else {
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Falha ao cadastrar prova');
            header("location: cadastro_provas.php");
            exit();
        }
    }
}