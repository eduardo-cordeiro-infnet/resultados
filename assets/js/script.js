$(document).ready(function() {
  // Menu Lateral
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
				break;
			case "coluna":
				$(".dashboard .area").addClass("coluna");
				break;
			case "grid":
				$(".dashboard .area").removeClass("lista");
				break;
			case "lista":
				$(".dashboard .area").addClass("lista");
				break;
			default:
				console.log("Nada");
		}
	});
});

$(window).load(function() {
	// Tooltip para os botões de ação
	$('.tiptool').tooltipster({
		'multiple': true,
		'minWidth': 180,
		'arrow': false
	});

	$('.ajax_list').on('click', function() {
		setTimeout(
			function() {
				$('.tiptool').tooltipster();
				console.log("list");
			}, 500
		);
	});
});

// Funções para obter os estilos finais calculados pelo navegador
// http://stackoverflow.com/a/5830517/1815558
CSSHelper = {
	css: function(a) {
		var sheets = document.styleSheets, o = {};
		for (var i in sheets) {
			var rules = sheets[i].rules || sheets[i].cssRules;
			for (var r in rules) {
				if (a.is(rules[r].selectorText)) {
					o = $.extend(o, CSSHelper.css2json(rules[r].style), CSSHelper.css2json(a.attr('style')));
				}
			}
		}
		return o;
	},

	css2json: function(css) {
		var s = {};
		if (!css) return s;
		if (css instanceof CSSStyleDeclaration) {
			for (var i in css) {
				if ((css[i]).toLowerCase) {
					s[(css[i]).toLowerCase()] = (css[css[i]]);
				}
			}
		} else if (typeof css == "string") {
			css = css.split("; ");
			for (var i in css) {
				var l = css[i].split(": ");
				s[l[0].toLowerCase()] = (l[1]);
			}
		}
		return s;
	}
};

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

RelatorioTurma.exportarExcel = function(e) {
	// Se não houver cópia da tabela para exportação, criar cópia
	if (!$('#relatorio_export').length)
	{
		// Impedir que o link seja aberto pelo navegador (ação default)
		e.preventDefault();

		// Exibir mensagem de "carregando"
		$('.mensagem_carregando').show()

		// Criar cópia da tabela do relatório com ID específico
		var $relatorioExport = $('.relatorio_container .relatorio').clone().attr('id', 'relatorio_export');

		// Remover coluna de numeração e imagens da tabela, para evitar erros no arquivo gerado
		$('.numero_linha, img', $relatorioExport).remove();

		// Incluir cópia da tabela no documento, para poder ser utilizada pelo plugin de exportação
		$('.relatorio_exportar_container').append($relatorioExport);

		window.setTimeout(function() {
			// Atribuir os estilos calculados diretamente a cada elemento da tabela
			// (processo intenso para o navegador, trava a página por aproximadamente 3 minutos)
			$('th, td, tr', '.relatorio_exportar_container').each(
				function() {
					//$(this).css(CSSHelper.css($(this)));
				}
			);

			// Dados gerais da disciplina da tabela superior
			var $linhasDadosDisciplina = $('.relatorio_cabecalho_linha tr').clone();

			// Ajustar os títulos dos dados gerais para alinhamento à direita
			$('th', $linhasDadosDisciplina).css('text-align', 'right');

			// Incluir linha vazia após os dados gerais
			$linhasDadosDisciplina.push($('<tr/>')[0]);

			// Incluir a tabela de dados gerais na cópia do relatório para exportação
			$('thead', $relatorioExport).prepend($linhasDadosDisciplina);

			// Gerar arquivo Excel para download
			var conteudoExcel = $().battatech_excelexport({
				containerid: 'relatorio_export',
				datatype: 'table',
				worksheetName: 'Resultados',
				returnUri: true
			});

			// Ocultar mensagem de "carregando"
			$('.mensagem_carregando').hide();

			// Atualizar o próprio link para passar a fazer download do arquivo e acionar o link
			// Isso é feito para que a propriedade download do link seja considerada,
			// definindo o nome do arquivo exportado e evitando a abertura de uma nova janela
			$(e.target).attr('href', conteudoExcel)[0].click();
		}, 50);

		return false;
	}

	// Se houver cópia da tabela, não é necessário fazer nada, apenas seguir o link clicado
	return true;
};

PrepararEstrutura = {};
// Altera a cor das linhas ao desmarcar as caixas de seleção.
PrepararEstrutura.incluirListenerCheckboxes = function() {
	$('.alteracoes-estrutura input[type=checkbox]').each(
		function () {
			$(this).change(
				function() {
					$(this).parents('tr').toggleClass('desmarcado')
				}
			);
		}
	);
};
