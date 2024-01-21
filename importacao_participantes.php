<?php

include_once "conexao.php";

if(isset($_FILES['arquivo'])){
    $arquivo = $_FILES['arquivo'];

$primeira_linha = true;
$linhas_importadas = 0;
$linhas_nao_importadas = 0;
$participantes_nao_importado = "";

if (pathinfo($arquivo['name'], PATHINFO_EXTENSION) === "csv") {

    $dados_arquivo = fopen($arquivo['tmp_name'], "r");

    while ($linha = fgetcsv($dados_arquivo, 1000, ";")) {

        if ($primeira_linha) {
            $primeira_linha = false;
            continue;
        }

        array_walk_recursive($linha, 'converter');

        $nome = $linha[0] ?? "NULL";
        $departamento_nome = $linha[1] ?? "NULL";

        $queryUsername = "SELECT nome FROM participantes WHERE nome = :nome";
        $consultanome = $pdo->prepare($queryUsername);
        $consultanome->bindParam(':nome', $nome);
        $consultanome->execute();

        if ($consultanome->rowCount() > 0) {
            $linhas_nao_importadas++;
            $participantes_nao_importado .= ", " . $nome;
        } else {

            $sqlDepartamento = "SELECT id FROM departamentos WHERE name = :departamento_nome";
            $consultaDepartamento = $pdo->prepare($sqlDepartamento);
            $consultaDepartamento->bindParam(':departamento_nome', $departamento_nome);
            $consultaDepartamento->execute();

            if ($consultaDepartamento->rowCount() == 0) {
                $linhas_nao_importadas++;
                $participantes_nao_importado .= ", " . $nome;
            } else {
                $departamento_id = $consultaDepartamento->fetchColumn();

                // Inserir participante na tabela
                $sql = "INSERT INTO participantes(nome, id_departamentos) VALUES(:nome, :id_departamentos)";
                $consulta = $pdo->prepare($sql);
                $consulta->bindParam(':nome', $nome);
                $consulta->bindParam(':id_departamentos', $departamento_id);

                if ($consulta->execute()) {
                    $linhas_importadas++;
                    header("location: importar_participantes.php");
                    exit();
                } else {
                    $linhas_nao_importadas++;
                    $participantes_nao_importado .= ", " . $nome;
                    echo "<script>alert('Erro!'); window.location.href='importar_participantes.php';</script>";
                }
            }
        }
    }
}

    function converter(&$dados_arquivo){
            
        $dados_arquivo = mb_convert_encoding($dados_arquivo, "UTF-8", "ISO-8859-1");
    }
}
?>