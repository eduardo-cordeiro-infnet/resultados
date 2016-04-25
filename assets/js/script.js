// Menu Lateral
$(document).ready(function() {
	$("#menu").mmenu({
		navbar: false,
		navbars: {
			height:4,
			content: [
				'<div class="user-card"><div class="user-photo"><img src="http://lorempixel.com/100/100/people/" /></div><div class="user-info"><h2>Fulano Silva</h2><p>fulano.silva@infnet.edu.br</p></div></div>'
			]
		}
	});

	// Altera o tipo de visualização
	$(".ui-menu button").click(function(){
		var rel = $(this).attr('rel');
		switch (rel) {
			case "linha":
				$(".dashboard .area").removeClass("coluna");
				console.log("linha");
				break;
			case "coluna":
				$(".dashboard .area").addClass("coluna");
				console.log("coluna");
				break;
			case "grid":
				$(".dashboard .area").removeClass("lista");
				console.log("grid");
				break;
			case "lista":
				$(".dashboard .area").addClass("lista");
				console.log("lista");
				break;
			default:
				console.log("Nada");
		}
	});

});

RelatorioTurma = {};
RelatorioTurma.formatarTabela = function() {
	// Definir largura da barra de rolagem superior para ficar igual à tabela já renderizada
	$('div.barra_rolagem_div_interna').width($('table.relatorio').width());

	// Quando a tabela do relatório rolar horizontalmente, refletir a rolagem na barra superior
	$('.barra_rolagem_container').scroll(function(){
    $('.relatorio_container')
        .scrollLeft($('.barra_rolagem_container').scrollLeft());
    });
    $('.relatorio_container').scroll(function(){
        $('.barra_rolagem_container')
            .scrollLeft($('.relatorio_container').scrollLeft());
    });

    // Destacar células mescladas nas colunas ao passar o cursor
	$('table.relatorio').wholly({
		highlightHorizontal: 'destaque',
		highlightVertical: 'destaque'
	})
};

RelatorioTurma.imprimirTabela = function() {
   var divToPrint=document.getElementsByClassName('relatorio')[0];
   newWin= window.open("");
   newWin.document.write(divToPrint.outerHTML);
   newWin.print();
   newWin.close();
}
