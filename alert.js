function alerta(tipo, mensagem) {
    Swal.fire({
        position: 'center',
        icon: tipo,
        title: mensagem,
        showConfirmButton: false,
        timer: 3000
    });
}