<?php
require_once "conexao.php";

if (isset($_POST["confirmacao"]) || isset($_POST["sessao"]) &&  isset($_POST["equipe"]) && isset($_POST["facilitador"]) && isset($_POST["participante"]) &&  isset($_POST["provas"])) {                                                                                                                                                                  
    if (empty($_POST["sessao"]) || empty($_POST["equipe"]) || empty($_POST["facilitador"]) || empty($_POST["participante"]) || empty($_POST["provas"])) {
        echo "<script>alert('Favor inserir todos os dados!'); window.location.href='gerenciamentoEdicao.php';</script>";
        exit();
    } else {
        $sessao = $_POST["sessao"];
        $equipe = $_POST["equipe"];
        $facilitador = $_POST["facilitador"];
        $participantes = $_POST["participante"];
        $provas = $_POST["provas"];

        $sqlsessao = "SELECT id FROM sessoes WHERE nome = :sessao order by data_criacao desc limit 1";
        $consultasessao = $pdo->prepare($sqlsessao);
        $consultasessao->bindParam(':sessao', $sessao);
        $consultasessao->execute();

        if ($consultasessao->rowCount() == 0) {
            echo "Erro: Sessão inválida.";
        } else {
            $sessao_id = $consultasessao->fetchColumn();
        }


        $sqlequipe = "SELECT id FROM equipes WHERE nome = :equipe";
        $consultaequipe = $pdo->prepare($sqlequipe);
        $consultaequipe->bindParam(':equipe', $equipe);
        $consultaequipe->execute();

        if ($consultaequipe->rowCount() == 0) {
            echo "Erro: Equipe inválida.";
        } else {
            $equipe_id = $consultaequipe->fetchColumn();
        }

        $sqlfacilitador = "SELECT id FROM usuarios WHERE nome = :facilitador";
        $consultafacilitador = $pdo->prepare($sqlfacilitador);
        $consultafacilitador->bindParam(':facilitador', $facilitador);
        $consultafacilitador->execute();

        if ($consultafacilitador->rowCount() == 0) {
            echo "Erro: Facilitador inválido.";
        } else {
            $facilitador_id = $consultafacilitador->fetchColumn();
        }


        $participantes_ids = array();

        foreach ($participantes as $participanteNome) {
            $sqlparticipantes = "SELECT id FROM participantes WHERE nome = :participante";
            $conparticipantes = $pdo->prepare($sqlparticipantes);
            $conparticipantes->bindParam(':participante', $participanteNome);
            $conparticipantes->execute();

            if ($conparticipantes->rowCount() == 0) {
                echo "Erro: Participante inválido: $participanteNome. <br>";
            } else {
                $participantes_id = $conparticipantes->fetchColumn();
                $participantes_ids[] = $participantes_id;
            }
        }



        $provas_ids = array();

        foreach ($provas as $provasNome) {
            $sqlprovas = "SELECT * FROM provas WHERE nome = :provas";
            $conprovas = $pdo->prepare($sqlprovas);
            $conprovas->bindParam(':provas', $provasNome);
            $conprovas->execute();

            if ($conprovas->rowCount() == 0) {
                echo "Erro: Participante inválido: $provasNome. <br>";
            } else {
                $provas_id = $conprovas->fetchColumn();
                $provas_ids[] = $provas_id;
            }
        }


        $sql = "INSERT INTO gerenciamento_sessao (id_sessoes, id_equipe, id_usuarios, id_participantes) VALUES (:id_sessoes, :id_equipe, :id_usuarios, :id_participantes)";
        $consulta = $pdo->prepare($sql);
        $consulta->bindParam(':id_sessoes', $sessao_id);
        $consulta->bindParam(':id_equipe', $equipe_id);
        $consulta->bindParam(':id_usuarios', $facilitador_id);

        foreach ($participantes_ids as $participante_id) {
            $consulta->bindParam(':id_participantes', $participante_id);
            $consulta->execute();
        }

        $sql1 = "INSERT INTO equipes_provas (id_sessao, id_equipes, id_provas, situacao, andamento) VALUES (:id_sessoes, :id_equipe, :id_provas, 'Pendente', 'Aguardando')";
        $consulta1 = $pdo->prepare($sql1);
        $consulta1->bindParam(':id_sessoes', $sessao_id);
        $consulta1->bindParam(':id_equipe', $equipe_id);


        foreach ($provas_ids as $provas_id) {
            $consulta1->bindParam(':id_provas', $provas_id);
            $consulta1->execute();
        }
    }
}