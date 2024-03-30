<?php

require_once "conexao.php";

if (isset($_POST["add_user"])) {
    if (empty($_POST["nome"]) || empty($_POST["departamentos"]) || empty($_POST["tipo"])) {
        echo "<script>alert('Favor inserir todos os dados!'); window.location.href='acesso.php';</script>";
        exit();
    } else {
        $nome = $_POST["nome"];
        $departamento_nome = $_POST["departamentos"];
        $tipo_nome = $_POST["tipo"];

        $queryUsername = "SELECT nome FROM usuarios WHERE nome = :nome";
        $consultanome = $pdo->prepare($queryUsername);
        $consultanome->bindParam(':nome', $nome);
        $consultanome->execute();

        if ($consultanome->rowCount() > 0) {
            if($tipo_nome === 'Gestor RH'){
                session_start();
                $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Gestor de RH ja existente!');
                header("location: acesso.php");
                exit();
                }elseif($tipo_nome === 'Facilitador'){
                    session_start();
                    $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Facilitador ja existente!');
                    header("location: acesso.php");
                    exit();  
            }           
        } else {
            if ($tipo_nome == "Gestor RH" || $tipo_nome == "Desenvolvedor") {
                $permissao = "admin";
            } else {
                $permissao = "limited";
            }

            $sqlVerificaTipo = "SELECT id FROM tipo WHERE tipo = :tipo";
            $consultaVerificaTipo = $pdo->prepare($sqlVerificaTipo);
            $consultaVerificaTipo->bindParam(':tipo', $tipo_nome);
            $consultaVerificaTipo->execute();

            if ($consultaVerificaTipo->rowCount() == 0) {
                echo "Erro: Tipo inválido.";
            } else {
                $tipo_id = $consultaVerificaTipo->fetchColumn();

                $sqlDepartamento = "SELECT id FROM departamentos WHERE name = :departamento_nome";
                $consultaDepartamento = $pdo->prepare($sqlDepartamento);
                $consultaDepartamento->bindParam(':departamento_nome', $departamento_nome);
                $consultaDepartamento->execute();

                if ($consultaDepartamento->rowCount() == 0) {
                    echo "Erro: Departamento inválido.";
                } else {
                    $departamento_id = $consultaDepartamento->fetchColumn();

                    if($permissao == 'limited'){

                        $hash = "facilitadorgazin";
                        $situacao = "Inativo";

                    }elseif($permissao == 'admin'){

                        $hash = "admingazin";
                        $situacao = "Ativo";
                    }

                    $senha = password_hash($hash, PASSWORD_DEFAULT);

                    $sql = "INSERT INTO usuarios(nome, senha, permission, situacao, id_departamentos, id_tipo, fotos, senha_resetada) VALUES(:nome, :senha, :permissao, :situacao, :id_departamentos, :id_tipo, :fotos, 'sim')";

                    $caminho_imagem_predefinida = 'semfoto.jpg';

                    $imagem_binaria = file_get_contents($caminho_imagem_predefinida);

                    $consulta = $pdo->prepare($sql);
                    $consulta->bindParam(':nome', $nome);
                    $consulta->bindParam(':senha', $senha);
                    $consulta->bindParam(':permissao', $permissao);
                    $consulta->bindParam(':situacao', $situacao);
                    $consulta->bindParam(':id_departamentos', $departamento_id);
                    $consulta->bindParam(':id_tipo', $tipo_id);
                    $consulta->bindParam(':fotos', $imagem_binaria, PDO::PARAM_LOB);

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
    
                        $insert = "INSERT INTO log_facilitadores (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?, 'adição de usuário' , NOW() , NULL ,?)";
                        $stmt = $pdo->prepare($insert);
                        $stmt->bindValue(1, $user);
                        $stmt->bindValue(2, $ip_user);
                        $stmt->bindValue(3, $nome);
                        $stmt->execute();

                        session_start();
                        $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Cadastrado com sucesso!');
                        header("location: acesso.php");
                        exit();
                    } else {
                        session_start();
                        $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Falha ao cadastrar usuário');
                        header("location: acesso.php");
                        exit();
                    }
                }
            }
        }
    }
}