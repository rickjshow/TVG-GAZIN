function alertaSucesso(tipo, mensagem) {
    Swal.fire({
        position: 'center',
        icon: tipo,
        title: mensagem,
        showConfirmButton: true,
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'home.php';
        }
    });
}