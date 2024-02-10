function alertNao() {
    Swal.fire({
        title: "Você tem certeza?",
        text: "Uma vez confirmado não será possível reverter!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sim, tenho certeza!",
        cancelButtonText: "Não, cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Aguarde...",
                text: "Enviando dados para o servidor.",
                icon: "info",
                showConfirmButton: false,
                allowOutsideClick: false
            });

            $.ajax({
                type: "POST",
                url: "adicionarEquipeSessao.php",  
                data: $('#meuFormulario').serialize(),  
                success: function (response) {
                    Swal.fire({
                        title: "Equipes inseridas",
                        text: "As equipes foram inseridas com sucesso",
                        icon: "success"
                    }).then(() => {
                        window.location.href = 'home.php';
                    });
                },
                error: function () {
                    Swal.fire({
                        title: "Erro!",
                        text: "Ocorreu um erro ao processar a solicitação.",
                        icon: "error"
                    });
                }
            });
        } else {
            $('#exampleModal').modal('show');
        }
    });
}