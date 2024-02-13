<?php

    include "conexao.php";


    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fotos"])) {

        $user = $_SESSION['username'];
        $queryId = "SELECT id FROM usuarios WHERE nome = :user";
        $consultaId = $pdo->prepare($queryId);
        $consultaId->bindParam(':user', $user);
        $consultaId->execute();
        $Id = $consultaId->fetch(PDO::FETCH_ASSOC);
        
        $querySessao = "SELECT id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
        $stmtSessao = $pdo->prepare($querySessao);
        $stmtSessao->execute();
        $IdSession = $stmtSessao->fetch(PDO::FETCH_ASSOC);

        if ($Id && $IdSession) {
            $id_usuario = $Id['id'];
            $id_sessao = $IdSession['id'];
            
            foreach ($_FILES["fotos"]["tmp_name"] as $key => $tmp_name) {
                $nome_arquivo = $_FILES["fotos"]["name"][$key];
                $conteudo_imagem = fopen($_FILES["fotos"]["tmp_name"][$key], 'rb');
    
                $queryInsert = "INSERT INTO galeria (imagem, id_sessoes, id_usuarios) VALUES (:imagem, :id_sessoes, :id_usuarios)";
                $stmtInsert = $pdo->prepare($queryInsert);
                $stmtInsert->bindParam(':imagem', $conteudo_imagem, PDO::PARAM_LOB);
                $stmtInsert->bindParam(':id_sessoes', $id_sessao);
                $stmtInsert->bindParam(':id_usuarios', $id_usuario);
                $stmtInsert->execute();
            }
        }
        if($stmtInsert){
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'success', 'mensagem' => 'Imagens inseridas com sucesso!');
            header("location: galeria.php");
            exit();
        }else{
            session_start();
            $_SESSION['alerta'] = array('tipo' => 'error', 'mensagem' => 'Erro ao inserir imagens!');
            header("location: galeria.php");
            exit(); 
        }
    }