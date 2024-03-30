<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once "conexao.php";

if (isset($_POST['submit'])) {
    if ($_FILES['planilha']['error'] == UPLOAD_ERR_OK) {
        $allowedFileTypes = array('csv', 'xlsx', 'xls', 'ods');
        $fileExtension = pathinfo($_FILES['planilha']['name'], PATHINFO_EXTENSION);

        if (!in_array(strtolower($fileExtension), $allowedFileTypes)) {
            exibirAlerta('error', 'Tipo de arquivo não suportado. Por favor, anexe uma planilha no formato .csv, .xlsx, .xls ou .ods.');
            exit();
        }

        $caminhoTemporario = $_FILES['planilha']['tmp_name'];

        $spreadsheet = IOFactory::load($caminhoTemporario);
        $sheet = $spreadsheet->getActiveSheet();

        $rowCount = $sheet->getHighestDataRow();
        if ($rowCount <= 1) {
            exibirAlerta('error', 'A planilha está vazia. Por favor, anexe uma planilha com dados.');
            exit();
        }

        $sqlSelectDepartamento = "SELECT id FROM departamentos WHERE name = :nome";
        $stmtSelectDepartamento = $pdo->prepare($sqlSelectDepartamento);

        $sqlInsertParticipante = "INSERT INTO participantes (nome, id_departamentos) VALUES (:nome, :id_departamento)";
        $stmtInsertParticipante = $pdo->prepare($sqlInsertParticipante);

        $duplicateNames = array();
        $nonexistentDepartments = array();

        $firstRow = true;

        foreach ($sheet->getRowIterator() as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue; 
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE);

            $nome = $cellIterator->current()->getValue();
            $cellIterator->next();
            $nomeDepartamento = $cellIterator->current()->getValue();

            if(empty($nome)){
                exibirAlerta('error', 'Existem campos de nome vazios na planilha!');

            }if(empty($nomeDepartamento)){
                
                exibirAlerta('error', 'Existem campos de departamento vazios na planilha');

            }

            $stmtSelectDepartamento->execute(['nome' => $nomeDepartamento]);
            $idDepartamento = $stmtSelectDepartamento->fetchColumn();

            if (!$idDepartamento) {
                $nonexistentDepartments[] = $nomeDepartamento;
                continue;
            }

            $stmtCheckNome = $pdo->prepare("SELECT COUNT(*) FROM participantes WHERE nome = :nome");
            $stmtCheckNome->execute(['nome' => $nome]);
            $count = $stmtCheckNome->fetchColumn();

            if ($count > 0) {
                $duplicateNames[] = $nome;
                continue;
            }

            $result = $stmtInsertParticipante->execute(['nome' => $nome, 'id_departamento' => $idDepartamento]);

            if($result){

                $sucessfulInserts[] = $nome;

            }elseif (!$result) {

                exibirAlerta('error', "Erro na inserção para $nome");
            }
        }

        $stmtSelectDepartamento = null;
        $stmtInsertParticipante = null;
        $stmtCheckNome = null;
        $pdo = null;

        if (!empty($nonexistentDepartments)) {
            $message = 'Os seguintes Departamentos não existem: <br>';
            foreach ($nonexistentDepartments as $department) {
                $message .= "<br> $department <br>";
                $message .= "<br>Ajuste apenas os Departamentos que estão incorretos e importe novamente! <br>";
            }
            exibirAlerta('error', $message);
        } elseif (!empty($duplicateNames)) {
            $message = 'Os seguintes participantes já existem no banco de dados: <br>';
            foreach ($duplicateNames as $name) {
                $message .= "<br>- $name <br>";
            }
            exibirAlerta('warning', $message);
        } else {
            $user = $_SESSION['username'];

                    require "conexao.php";

                    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
                    } else {
                        $ip_address = $_SERVER['REMOTE_ADDR'];
                    }
                    
                    $ip_user = filter_var($ip_address, FILTER_VALIDATE_IP);

                    $querySession = "SELECT id FROM sessoes WHERE situacao = 'Pendente'";
                    $sessao = $pdo->prepare($querySession);
                    $sessao->execute();
                    $resultado = $sessao->fetchColumn();
                    if($resultado > 0){
                        $idSession = $resultado;
                    }else{
                        $idSession = null;
                    }

                foreach($sucessfulInserts AS $name){
                    $insert = "INSERT INTO log_participantes (usuario, ip_user, acao, horario, valor_antigo, valor_novo) VALUES (?,?, 'importação de participantes' , NOW() , NULL ,?)";
                    $stmt = $pdo->prepare($insert);
                    $stmt->bindValue(1, $user);
                    $stmt->bindValue(2, $ip_user);
                    $stmt->bindValue(3, $name);
                    $stmt->execute();
                }

            exibirAlerta('success', 'Cadastrado com sucesso!');
        }
    } else {
        exibirAlerta('error', 'Por favor, anexe alguma planilha');
    }
}

function exibirAlerta($tipo, $mensagem) {
    session_start();
    $_SESSION['alerta'] = array('tipo' => $tipo, 'mensagem' => $mensagem);
    header("location: importar_participantes.php");
    exit();
}
?>