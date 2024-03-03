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

        $firstRow = true; // Variável de controle para ignorar a primeira linha

        foreach ($sheet->getRowIterator() as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue; // Ignora a primeira linha
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE);

            $nome = $cellIterator->current()->getValue();
            $cellIterator->next();
            $nomeDepartamento = $cellIterator->current()->getValue();

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

            if (!$result) {
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
