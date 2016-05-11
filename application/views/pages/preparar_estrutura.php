<?php
	echo validation_errors();

	echo form_open(
		str_replace('preparar_estrutura', 'atualizar_estrutura', uri_string()),
		array(
			'class' => 'alteracoes-estrutura',
			'onsubmit' => 'return PrepararEstrutura.confirmarAtualizacaoEstrutura();'
		)
	);

	foreach ($alteracoes_estrutura as $tipo_item => $alteracoes):
		if ($tipo_item === 'turmas'):
?>
	<h2>Turmas</h2>
	<table>
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th>Ação</th>
				<th>Bloco</th>
				<th>Denominação</th>
				<th>Disciplina</th>
				<th>Curso no Moodle</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($alteracoes as $index => $alteracao):
				$turma = $alteracao['elemento'];
				$operacao = $alteracao['operacao'];
			?>
			<tr <?php echo ($operacao === 'manter') ? 'class="desmarcado"' : ''; ?>>
				<td class="clicar-checkbox">
					<?php
					if ($operacao !== 'manter')
					{
						echo form_checkbox(
							implode('-', array($tipo_item, $index)),
							$operacao,
							true
						);
					}
					?>
				</td>
				<td class="<?php echo $operacao; ?>"><?php echo $alteracao['descricao']; ?></td>
				<td><?php echo $turma->disciplina->bloco->nome; ?></td>
				<td><?php echo $turma->disciplina->denominacao_bloco; ?></td>
				<td>
					<?php
					echo $turma->disciplina->nome;
					if ($operacao !== 'cadastrar')
					{
						echo anchor_popup(
							site_url('cadastros/classe/turmas/' . $turma->classe->id . '/edit/' . $turma->id),
							img('/assets/grocery_crud/themes/struct/css/images/ic_mode_edit_black_24px.svg'),
							array('title' => 'Abrir cadastro da turma')
						);
					}
					?>
				</td>
				<td>
					<?php
						echo (isset($turma->id_mdl_course)) ? anchor_popup($alteracao['link_moodle'], $alteracao['caminho_curso_moodle']) : nbs();
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
		endif;
	endforeach;

	echo form_submit(null, 'Aplicar ações selecionadas', 'class="btn btn-primary"');
	echo form_button(null, 'Voltar para o cadastro', 'class="voltar-cadastro btn btn-default"');
	echo form_close();
	?>

	<div id="modal-nenhuma-acao-selecionada" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Nenhuma ação selecionada</h4>
				</div>
				<div class="modal-body">
					<p>Por favor selecione uma ou mais opções para aplicar alterações na estrutura.</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
				</div>
			</div>
		</div>
	</div>


	<div id="modal-confirmar-alteracao-estrutura" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Confirmar alterações</h4>
				</div>
				<div class="modal-body">
					<p>Selecionada(s) <span class="qtd-alteracoes-selecionadas"></span> alteração(s).</p>
					<p>Deseja aplicar? Esta ação não pode ser desfeita.</p>
				</div>
				<div class="modal-footer">
					<button type="button" id="confirmar-atualizacao-estrutura" class="btn btn-primary" data-dismiss="modal">Confirmar</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
