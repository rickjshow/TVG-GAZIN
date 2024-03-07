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
            max-width: 500px;
            margin: 50px auto;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            transition: box-shadow 0.3s ease-in-out;
        }
        input.form-control.mr-2, button.btn.btn-outline-secondary.checklist-button {
            font-size: 13px;
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
            flex-grow: 4; 
            word-wrap: break-word;
            margin-top: 12px;
            transition: text-decoration 0.3s ease-in-out;
            font-size: 10px;
        }

        .checklist-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            font-size: 12px;
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
            padding: 6px;
       
        }

        .not-done {
            background-color: #FFEBB3;
            padding: 9px;
            border: solid 1px black;
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
            padding: 6px !important;
        }

        .status-button {
            margin-left: 5px;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            transition: background-color 0.3s ease-in-out;
            font-size:10px;
        }

        .status-button.done {
            background-color: #28a745;
            color: #fff;
        }

        .status-button.not-done { 
            color: #fff;
        }


        .invisible-checkbox {
            display: none;
        }

        
        .orange-button {
            background-color: #ff7a00; 
            color: #fff; 
        }

        .orange-button:hover {
            background-color: #ff7a00;
        }

        .red-button{
            background-color: red;
        }

        .task label:hover {
            cursor: pointer;
        }
        .task label{


            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif

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
            <h2 class="text-center" style="font-size: 20px;">Adicione as tarefas</h2>

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
                         <div class="task" data-taskid="<?php echo $tarefa['id']; ?>">
                        <input type="checkbox" class="invisible-checkbox" <?php echo $tarefa['situacao'] == 'Concluída' ? 'checked' : ''; ?>>
                        <label class="<?php echo $tarefa['situacao'] == 'Concluída' ? 'done' : 'not-done';  ?>">
                            <?php echo $tarefa['nome'];  ?>
                        </label>
                        <div class="status-buttons">
                            <button class="status-button done" onclick="markTaskAsDone(this)">Concluído</button>
                            <button class="status-button not-done orange-button" onclick="markTaskAsNotDone(this)">Pendente</button>
                            <button class="status-button not-done red-button" onclick="deleteTask(this)" data-taskid="<?php echo $tarefa['id']; ?>">Excluir</button>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>

function updateTaskStatus(checkbox) {
    var taskId = $(checkbox).closest('.task').data('taskid');
    var newStatus = checkbox.checked ? 'Concluída' : 'Pendente';

    console.log(taskId);

    $.ajax({
        type: "POST",
        url: "atualizar_situacao_tarefa.php",
        data: { taskId: taskId, newStatus: newStatus },
        success: function(response) {
            if (response.trim() !== '') {
                try {
                    var result = JSON.parse(response);
                 
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Tarefa Atualizada!',
                            showConfirmButton: false,
                            timer: 1000 
                        }).then(function () {
                            location.reload();
                        })
                     
                    } else {
                        console.error('Falha ao atualizar o status da tarefa:', result.mensagem);
                    }
                } catch (e) {
                    console.error('Erro ao fazer o parse da resposta JSON:', e);
                    console.error('Resposta do servidor:', response);
                }
            } else {
                console.error('Resposta vazia do servidor.');
            }
        },
        error: function() {
            console.error('Erro ao enviar solicitação AJAX para atualizar o status da tarefa.');
        }
    });
}

function deleteTask(button) {
    var taskId = $(button).closest('.task').data('taskid');

    $.ajax({
    type: "POST",
    url: "excluir_tarefa.php",
    data: { Id: taskId },
    success: function(response) {
        if (response.trim() !== '') {
            try {
                var result = JSON.parse(response);

                if (Swal.fire) {
                    Swal.fire({
                        title: 'Você tem certeza?',
                        text: 'Isso irá excluir a tarefa!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sim, finalizar!',
                        cancelButtonText: 'Cancelar'
                    }).then(function (confirmed) {
                        if (confirmed.value) {
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Tarefa Excluída!',
                                    showConfirmButton: false,
                                    timer: 1000
                                }).then(function () {
                                    location.reload();
                                });
                            } else {
                                console.error('Falha ao excluir a tarefa:', result.mensagem);
                            }
                        }
                    });
                }
            } catch (e) {
                console.error('Erro ao fazer o parse da resposta JSON:', e);
                console.error('Resposta do servidor:', response);
            }
        } else {
            console.error('Resposta vazia do servidor.');
        }
    },
    error: function() {
        console.error('Erro ao enviar solicitação AJAX para excluir a tarefa.');
    }
});

}


</script>

<script>

    function markTaskAsDone(button) {
        var checkbox = button.parentElement.previousElementSibling;
        checkbox.checked = true;
        updateTaskStatus(checkbox);
        button.parentElement.parentElement.classList.add('done');
        button.parentElement.parentElement.classList.remove('not-done');
    }

    function markTaskAsNotDone(button) {
        var checkbox = button.parentElement.previousElementSibling;
        checkbox.checked = false;
        updateTaskStatus(checkbox);
        button.parentElement.parentElement.classList.add('not-done');
        button.parentElement.parentElement.classList.remove('done');
    }
</script>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.17.0/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>

</body>
</html>