<?php 

    require_once "conexao.php";

    print_r($_POST);

    if(isset($_POST['id'])){
        $id = $_POST['id'];
    }

    $username = $_SESSION['username'];

            $queryUser = "SELECT id, permission FROM usuarios WHERE nome = :username";
            $stmtUser = $pdo->prepare($queryUser);
            $stmtUser->bindParam(":username", $username);
            $stmtUser->execute();

            $resultUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

            $querySessao = "SELECT nome, id FROM sessoes WHERE situacao = 'Pendente' ORDER BY data_criacao DESC LIMIT 1";
            $stmtSessao = $pdo->prepare($querySessao);
            $stmtSessao->execute();
            $nomeSessao = $stmtSessao->fetch(PDO::FETCH_ASSOC);

            if(isset($nomeSessao['id'])){
                $idSessao = $nomeSessao['id'];
            }

            $userId = $resultUser['id'];

            $queryProvas = "SELECT COUNT(*) FROM equipes_provas WHERE id_sessao = :id_sessao AND id_provas = :id_provas AND andamento = 'Execultando'";
            $consulta = $pdo->prepare($queryProvas);
            $consulta->bindParam(":id_sessao", $idSessao);
            $consulta->bindParam("id_provas", $id);
            $consulta->execute();
            $resultado = $consulta->fetchColumn();



            if($resultado > 0){
               echo "<div class='row mt-4'>
                    <div class='col-md-6 offset-md-3'>
                        <div id='pontuacaoCard' class='card text-center'>
                            <div class='card text-center'>
                                <div class='card-body'>
                                    <div class='form-group mt-3'>
                                        <label for='valorPontuacao'>Adicionar Valor:</label>
                                        <select class='form-control select2' id='valorPontuacao'>
                                            <option value='10'>10</option>
                                            <option value='15'>15</option>
                                            <option value='20'>20</option>
                                            <option value='25'>25</option>
                                            <option value='30'>30</option>
                                            <option value='35'>35</option>
                                            <option value='40'>40</option>
                                            <option value='45'>45</option>
                                            <option value='50'>50</option>
                                            <option value='55'>55</option>
                                            <option value='60'>60</option>
                                            <option value='65'>65</option>
                                            <option value='70'>70</option>
                                            <option value='75'>75</option>
                                            <option value='80'>80</option>
                                            <option value='85'>85</option>
                                            <option value='90'>90</option>
                                            <option value='95'>95</option>
                                            <option value='100'>100</option>
                                            <option value='120'>120</option>
                                            <option value='150'>150</option>
                                            <option value='200'>200</option>
                                        </select>
                                    </div>
                                    <button class='btn btn-success' onclick='adicionarPontuacao()'>Adicionar Pontuação</button>
                                    <input type='hidden' id='inputPontuacao' name='inputPontuacao'>
                                </div>

                                <div class='form-group mt-3'>
                                    <ul id='pontuacaoList' class='list-group'>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
            <div class='mt-4'></div>
            <div class='mt-4'></div>
        </div>";
    }else{
        echo "Deu ruim";
    }

?>