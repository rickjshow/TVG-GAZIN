<?php

require_once "conexao.php";

if (isset($_POST["add_user"])) {
    if (empty($_POST["nome"]) || empty($_POST["senha"]) || empty($_POST["departamentos"]) || empty($_POST["tipo"])) {
        echo "<script>alert('Favor inserir todos os dados!'); window.location.href='acesso.php';</script>";
        exit();
    } else {
        $nome = $_POST["nome"];
        $senha = $_POST["senha"];
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
            if ($tipo_nome === "GESTOR RH") {
                $situacao = "admin";
            } else {
                $situacao = "limited";
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

                    $sql = "INSERT INTO usuarios(nome, senha, permission, situacao, id_departamentos, id_tipo, fotos) VALUES(:nome, :senha, :situacao, 'Ativo', :id_departamentos, :id_tipo, :fotos)";

                    $caminho_imagem_predefinida = 'semfoto.jpg';

                    $imagem_binaria = file_get_contents($caminho_imagem_predefinida);

                    $consulta = $pdo->prepare($sql);
                    $consulta->bindParam(':nome', $nome);
                    $consulta->bindParam(':senha', $senha);
                    $consulta->bindParam(':situacao', $situacao);
                    $consulta->bindParam(':id_departamentos', $departamento_id);
                    $consulta->bindParam(':id_tipo', $tipo_id);
                    $consulta->bindParam(':fotos', $imagem_binaria, PDO::PARAM_LOB);

                    if ($consulta->execute()) {
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
