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
PrepararEstrutura.registrarListeners = function() {
	PrepararEstrutura.registrarListenersBotoes();
	PrepararEstrutura.registrarListenersCheckboxes();
}

PrepararEstrutura.registrarListenersBotoes = function() {
	$('.voltar-cadastro').click(
		function() {
			window.location.href = window.location.href.replace('preparar_estrutura/', '');
		}
	);

	$('#confirmar-atualizacao-estrutura').click(
		function() {
			document.getElementsByClassName('alteracoes-estrutura')[0].submit();
		}
	);
}

PrepararEstrutura.registrarListenersCheckboxes = function() {
	// Ao clicar na célula que contém o checkbox, marcar/desmarcar o checkbox
	$('.alteracoes-estrutura input:checkbox').each(
		function () {
			var $chk = $(this);

			$chk.parent().click(
				function(e) {
					var chk = $('input:checkbox', this)[0];

					// Não executar quando o próprio checkbox é clicado, para evitar marcação "dupla"
					if(chk && e.target != chk)
					{
						chk.click();
					}
				}
			);
		}
	);

	// Ao clicar no checkbox do cabeçalho, marcar ou desmarcar todas os checkboxes da tabela
	$('.alteracoes-estrutura th input:checkbox').change(
		function () {
			var $chk = $(this);
			var estadoChks = ($chk.is(':checked')) ? ':not(:checked)' : ':checked';

			$('td input:checkbox' + estadoChks, $chk.parents('table')).click();
		}
	);

	$('.alteracoes-estrutura td input:checkbox').each(
		function () {
			var $chk = $(this);

			// Define a cor das linhas de acordo com as checkboxes marcadas
			if ($chk.is(':checked')) {
				$chk.parents('tr').removeClass('desmarcado');
			} else {
				$chk.parents('tr').addClass('desmarcado');
			}

			$chk.change(
				function() {
					// Altera a cor das linhas ao marcar/desmarcar as checkboxes.
					$chk.parents('tr').toggleClass('desmarcado');
					PrepararEstrutura.atualizarCheckboxesDependentes(this);
				}
			);
		}
	);
};

PrepararEstrutura.atualizarCheckboxesDependentes = function(chk) {
	var $chk = $(chk);
	var rubrica = $chk.attr('name').substr(0, $chk.attr('name').indexOf('-')) === 'rubricas';

	// Checkboxes de atualização não interferem na dependência de outros checkboxes, exceto em caso de rubricas
	if ($chk.val() !== 'atualizar' || rubrica)
	{
		// Se o checkbox estiver sendo marcado ou desmarcado, os outros checkboxes sofrem a mesma ação
		var marcar = $(chk).is(':checked');

		// Se for marcação de cadastro ou desmarcação de remoção, aplicar ao checkbox do qual ele depende
		// Senão, aplicar a marcação ou desmarcação aos checkboxes dependentes
		var atributo = (($chk.val() === 'cadastrar' && marcar) || ($chk.val() === 'remover' && !marcar) || rubrica) ? 'name' : 'dependencia';
		var atributo_chk = (atributo === 'dependencia') ? 'name' : 'dependencia';

		$('[' + atributo + '="' + $chk.attr(atributo_chk) + '"]' + ((marcar) ? ':not(:checked)' : ':checked')).click();
	}
};

PrepararEstrutura.confirmarAtualizacaoEstrutura = function() {
	var qtdAlteracoesSelecionadas = $('.alteracoes-estrutura td input:checkbox:checked').length;
	var divModal = (qtdAlteracoesSelecionadas > 0) ? 'confirmar-alteracao-estrutura' : 'nenhuma-acao-selecionada';

	$('.qtd-alteracoes-selecionadas').html(qtdAlteracoesSelecionadas);
	$('#modal-' + divModal).modal();

	return false;
};
