let temporizador;
let tempoInicial;
let tempoPausado;
let tempoTotal = 40 * 60; 
let temporizadorAtivo = false;

function salvarEstadoTemporizador() {
    if (temporizadorAtivo) {
        const tempoAtual = new Date().getTime();
        const tempoDecorrido = Math.floor((tempoAtual - tempoInicial + tempoPausado) / 1000);
        localStorage.setItem('temporizador_estado', JSON.stringify({
            tempoDecorrido: tempoDecorrido,
            tempoPausado: tempoPausado,
            temporizadorAtivo: temporizadorAtivo
        }));
    }
}

function carregarEstadoTemporizador() {
    const estadoSalvo = localStorage.getItem('temporizador_estado');
    if (estadoSalvo) {
        const estado = JSON.parse(estadoSalvo);
        tempoPausado = estado.tempoPausado;
        temporizadorAtivo = estado.temporizadorAtivo;
        if (temporizadorAtivo) {
            const tempoAtual = new Date().getTime();
            const tempoDecorrido = estado.tempoDecorrido;

            const tempoRestante = tempoTotal - tempoDecorrido;

            if (tempoRestante > 0) {
                tempoInicial = tempoAtual - tempoDecorrido * 1000;
                temporizador = setInterval(atualizarTemporizador, 1000);
            } else {
                localStorage.removeItem('temporizador_estado');
            }
        }
    }
}



function atualizarTemporizador() {
    const tempoAtual = new Date().getTime();
    const tempoDecorrido = Math.floor((tempoAtual - tempoInicial + tempoPausado) / 1000);

    if (tempoDecorrido >= tempoTotal) {
        clearInterval(temporizador);
        temporizador = null;
        temporizadorAtivo = false;
        alert("Tempo esgotado!");
        localStorage.removeItem('temporizador_estado');
    } else {
        const tempoRestante = tempoTotal - tempoDecorrido;
        const minutosRestantes = Math.floor(tempoRestante / 60);
        const segundosRestantes = tempoRestante % 60;

        const tempoFormatado = `${String(minutosRestantes).padStart(2, '0')}:${String(segundosRestantes).padStart(2, '0')}`;
        $('#temporizador').text(tempoFormatado);
        salvarEstadoTemporizador();
    }
}

function iniciarTemporizador() {
    if (!temporizadorAtivo) {
        tempoInicial = new Date().getTime();
        temporizador = setInterval(atualizarTemporizador, 1000);
        temporizadorAtivo = true;
        salvarEstadoTemporizador();
    }
}

function pausarTemporizador() {
    clearInterval(temporizador);
    temporizador = null;
    temporizadorAtivo = false;
    const tempoPausa = new Date().getTime();
    tempoPausado += tempoPausa - tempoInicial;
    salvarEstadoTemporizador();
}



let confirmacaoAberta = false;

function finalizarTemporizador() {
    if (temporizadorAtivo) {
        pausarTemporizador();

        Swal.fire({
            title: 'Você tem certeza?',
            text: 'Isso finalizará o temporizador e lançará os pontos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, finalizar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                confirmacaoAberta = false;
                finalizarAposConfirmacao();
            } else {
                iniciarTemporizador(); 
            }
        });

        confirmacaoAberta = true;
    } else {
        
        finalizarAposConfirmacao();
    }
}


function finalizarAposConfirmacao() {

    if (confirmacaoAberta) {
        return;
    }

    clearInterval(temporizador);
    temporizador = null;
    temporizadorAtivo = false;

    const tempoFinal = new Date().getTime();
    const tempoDecorrido = Math.floor((tempoFinal - tempoInicial + tempoPausado) / 1000);

    const minutosDecorridos = Math.floor(tempoDecorrido / 60);
    const segundosDecorridos = tempoDecorrido % 60;

    const tempoDecorridoFormatado = `${String(minutosDecorridos).padStart(2, '0')}:${String(segundosDecorridos).padStart(2, '0')}`;

    $('#temporizador').text(tempoDecorridoFormatado);

    const urlParams = new URLSearchParams(window.location.search);
    const idProva = urlParams.get('id');

    $.ajax({
        type: 'POST',
        url: 'lancarPontos.php',
        data: { tempoFinalEmSegundos: tempoDecorrido, idProva: idProva }
    }).done(function(response) {
        console.log(response);
    }).fail(function(error) {
        console.error('Erro na requisição AJAX:', error);
    }).always(function() {
        Swal.close(); 
        localStorage.removeItem('temporizador_estado');
    });
}


$('#finalizar').click(function () {
    finalizarTemporizador();
});


    const tempoFormatado = `${String(Math.floor(tempoTotal / 60)).padStart(2, '0')}:${String(tempoTotal % 60).padStart(2, '0')}`;
    $('#temporizador').text(tempoFormatado);
    tempoPausado = 0;
    localStorage.removeItem('temporizador_estado');


$('#iniciar').click(function () {
    iniciarTemporizador();
});

$('#pausar').click(function () {
    pausarTemporizador();
});

$('#finalizar').click(function () {
    finalizarTemporizador();
});

$(document).ready(function () {
    carregarEstadoTemporizador();
});