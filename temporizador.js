let temporizador;
let tempoInicial;
let tempoPausado;
let tempoTotal = 40 * 60; // Inicialmente, 40 minutos convertidos para segundos
let temporizadorAtivo = false;

function atualizarTemporizador() {
    const tempoAtual = new Date().getTime();
    const tempoDecorrido = Math.floor((tempoAtual - tempoInicial + tempoPausado) / 1000);

    if (tempoDecorrido >= tempoTotal) {
        clearInterval(temporizador);
        temporizador = null;
        temporizadorAtivo = false;
        alert("Tempo esgotado!");
    } else {
        const tempoRestante = tempoTotal - tempoDecorrido;
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

    const tempoFinalEmSegundos = tempoDecorrido;

    console.log('Tempo Final (segundos):', tempoFinalEmSegundos);

    // Enviar para o arquivo PHP
    $.ajax({
        type: 'POST',
        url: 'lancarPontos.php',
        data: { tempoFinalEmSegundos: tempoFinalEmSegundos }
    })
    .done(function(response) {
        console.log(response);
    })
    .fail(function(error) {
        console.error('Erro na requisição AJAX:', error);
    });
}

function resetarTemporizador() {
    tempoTotal = 40 * 60; 
    const tempoFormatado = `${String(Math.floor(tempoTotal / 60)).padStart(2, '0')}:${String(tempoTotal % 60).padStart(2, '0')}`;
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
