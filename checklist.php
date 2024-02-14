<?php
require_once "conexao.php";
include "header.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="alert.js"></script>
    <title>Lista de Facilitadores</title>
    <style>
        .todo-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            transition: box-shadow 0.3s ease-in-out;
        }

        .task {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
        }

        .task input[type="checkbox"] {
            margin-right: 10px;
        }

        .task label {
            flex-grow: 1;
            word-wrap: break-word;
            margin-top: 12px;
            transition: text-decoration 0.3s ease-in-out;
        }

        .checklist-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            font-size: 14px;
        }

        .checklist-button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .checklist-button:hover {
            background-color: #0056b3;
        }

        .done {
            background-color: #d4edda;
            /* Cor verde */
        }

        .not-done {
            background-color: #f8d7da;
            padding: 6px 
        }

        #newTask {
            width: 70%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            transition: border-color 0.2s ease-in-out;
        }

        #newTask:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .status-buttons {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
        }

        .status-button {
            margin-left: 5px;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            transition: background-color 0.3s ease-in-out;
        }

        .status-button.done {
            background-color: #28a745;
            color: #fff;
        }

        .status-button.not-done {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="container mt-4">
        <div class="box1 mt-4 text-center p-4 border rounded shadow">
            <h3 class="mt-4 font-weight-bold text-primary" style="font-size: 18px;">Tarefas</h3>
        </div>
    </div>

    <div class="container mt-4">
        <div class="container mt-4 todo-container">
            <h2 class="text-center">Adicione as tarefas</h2>

            <form id="taskForm" action="adicionar_tarefa.php" method="post">
                    <div class="input-group mb-3">
                        <input type="text" name="newTask" class="form-control mr-2" placeholder="Adicionar nova tarefa" aria-label="Nova tarefa" aria-describedby="taskIcon">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary checklist-button" type="submit">Adicionar</button>
                        </div>
                    </div>
            </form>


            <?php
            $tarefasretorno = "SELECT * FROM tarefas";
            $consulta = $pdo->prepare($tarefasretorno);
            $consulta->execute();
            $data = $consulta->fetchAll(PDO::FETCH_ASSOC);

            if ($data) {
                foreach ($data as $tarefa) {
            ?>
                    <div class="task">
                        <input type="checkbox" <?php echo $tarefa['situacao'] == 'Concluída' ? 'checked' : ''; ?>>
                        <label class="<?php echo $tarefa['situacao'] == 'Concluída' ? 'done' : 'not-done'; ?>">
                            <?php echo $tarefa['nome']; ?>
                        </label>
                        <div class="status-buttons">
                            <button class="status-button done" onclick="markTaskAsDone(this, <?php echo $tarefa['id']; ?>)">Concluído</button>
                            <button class="status-button not-done" onclick="markTaskAsNotDone(this, <?php echo $tarefa['id']; ?>)">Pendente</button>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "Não existe tarefas Pendentes";
            }
            ?>

            <?php
            if (isset($_SESSION['alerta'])) {
                echo "<script>
                        alerta('{$_SESSION['alerta']['tipo']}', '{$_SESSION['alerta']['mensagem']}');
                        </script>";
                unset($_SESSION['alerta']);
            }
            ?>

            <div class="checklist-buttons mt-2">
                <button class="checklist-button" onclick="clearTasks()">Limpar Tarefas</button>
            </div>
        </div>
    </div>

    <script>
        function markTaskAsDone(button, taskId) {
            var checkbox = button.parentElement.previousElementSibling;
            checkbox.checked = true;
            updateTaskStatus(checkbox);

            // Adiciona a classe de estilo "done"
            button.parentElement.parentElement.classList.add('done');
            // Remove a classe de estilo "not-done" (caso ela exista)
            button.parentElement.parentElement.classList.remove('not-done');

            // Aqui você pode adicionar uma lógica para atualizar o status no banco de dados
            // usando uma solicitação AJAX semelhante ao método addTask
        }

        function markTaskAsNotDone(button, taskId) {
            var checkbox = button.parentElement.previousElementSibling;
            checkbox.checked = false;
            updateTaskStatus(checkbox);

            // Adiciona a classe de estilo "not-done"
            button.parentElement.parentElement.classList.add('not-done');
            // Remove a classe de estilo "done" (caso ela exista)
            button.parentElement.parentElement.classList.remove('done');

            // Aqui você pode adicionar uma lógica para atualizar o status no banco de dados
            // usando uma solicitação AJAX semelhante ao método addTask
        }
    </script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.17.0/font/bootstrap-icons.css" rel="stylesheet">

    


</body>
</html>

