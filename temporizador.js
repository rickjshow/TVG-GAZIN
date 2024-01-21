let temporizador;
let tempoInicial;
let tempoPausado;
let tempoTotal = { minutos: 40, segundos: 0 };
let temporizadorAtivo = false;

function atualizarTemporizador() {
    const tempoAtual = new Date().getTime();
    const tempoDecorrido = Math.floor((tempoAtual - tempoInicial + tempoPausado) / 1000);

    if (tempoDecorrido >= tempoTotal.minutos * 60 + tempoTotal.segundos) {
        clearInterval(temporizador);
        temporizador = null;
        temporizadorAtivo = false;
        alert("Tempo esgotado!");
    } else {
        const tempoRestante = tempoTotal.minutos * 60 + tempoTotal.segundos - tempoDecorrido;
        const minutosRestantes = Math.floor(tempoRestante / 60);
        const segundosRestantes = tempoRestante % 60;

        const tempoFormatado = `${String(minutosRestantes).padStart(2, '0')}:${String(segundosRestantes).padStart(2, '0')}`;
        $('#temporizador').text(tempoFormatado);
    }
}

function iniciarTemporizador() {
    if (!temporizadorAtivo) {
        tempoInicial = new Date().getTime();
        temporizador = setInterval(atualizarTemporizador, 1000);
        temporizadorAtivo = true;
    }
}

function pausarTemporizador() {
    clearInterval(temporizador);
    temporizador = null;
    temporizadorAtivo = false;
    const tempoPausa = new Date().getTime();
    tempoPausado += tempoPausa - tempoInicial;
}

function finalizarTemporizador() {
    clearInterval(temporizador);
    temporizador = null;
    temporizadorAtivo = false;
    const tempoFinal = new Date().getTime();
    const tempoDecorrido = Math.floor((tempoFinal - tempoInicial + tempoPausado) / 1000);

    tempoTotal.minutos = Math.floor(tempoDecorrido / 60);
    tempoTotal.segundos = tempoDecorrido % 60;

    const tempoFormatado = `${String(tempoTotal.minutos).padStart(2, '0')}:${String(tempoTotal.segundos).padStart(2, '0')}`;
    $('#temporizador').text(tempoFormatado);

    alert(`Tempo final: ${tempoTotal.minutos} minutos e ${tempoTotal.segundos} segundos`);
}

function resetarTemporizador() {
    tempoTotal = { minutos: 40, segundos: 0 };
    const tempoFormatado = `${String(tempoTotal.minutos).padStart(2, '0')}:${String(tempoTotal.segundos).padStart(2, '0')}`;
    $('#temporizador').text(tempoFormatado);
    temporizadorAtivo = false;
    tempoPausado = 0;
}

$('#iniciar').click(function () {
    iniciarTemporizador();
});

$('#pausar').click(function () {
    pausarTemporizador();
});

$('#finalizar').click(function () {
    finalizarTemporizador();
});

$('#resetar').click(function () {
    resetarTemporizador();
});

$(document).ready(function () {
    resetarTemporizador();
});



