<?php
	$botao_voltar = form_button(null, 'Voltar para o cadastro', 'class="voltar-cadastro btn btn-default"');
	echo $botao_voltar;

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
				<th>
				<?php
				echo form_checkbox(
					null,
					null,
					true
				);
				?>
				</th>
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
				<td>
					<?php
					if ($operacao !== 'manter')
					{
						echo form_checkbox(
							implode('-', array($tipo_item, $index)),
							$operacao,
							true,
							$alteracao['atributos']
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
		if ($tipo_item === 'avaliacoes'):
		?>
	<h2>Avaliações</h2>
	<table>
		<thead>
			<tr>
				<th>
				<?php
				echo form_checkbox(
					null,
					null,
					true
				);
				?>
				</th>
				<th>Ação</th>
				<th>Disciplina</th>
				<th>Avaliação</th>
				<th>Avaliação final</th>
				<th>Módulos no Moodle</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($alteracoes as $index => $alteracao):
				$avaliacao = $alteracao['elemento'];
				$operacao = $alteracao['operacao'];
			?>
			<tr <?php echo ($operacao === 'manter') ? 'class="desmarcado"' : ''; ?>>
				<td>
					<?php
					if ($operacao !== 'manter')
					{
						echo form_checkbox(
							implode('-', array($tipo_item, $index)),
							$operacao,
							true,
							$alteracao['atributos']
						);
					}
					?>
				</td>
				<td class="<?php echo $operacao; ?>"><?php echo $alteracao['descricao']; ?></td>
				<td><?php echo $avaliacao->turma->disciplina->nome; ?></td>
				<td>
					<?php
					echo $avaliacao->nome;
					if ($operacao !== 'cadastrar')
					{
						echo anchor_popup(
							site_url('cadastros/classe/avaliacoes/' . $avaliacao->turma->id . '/edit/' . $avaliacao->id),
							img('/assets/grocery_crud/themes/struct/css/images/ic_mode_edit_black_24px.svg'),
							array('title' => 'Abrir cadastro da avaliação')
						);
					}
					?>
				</td>
				<td><?php echo ($avaliacao->avaliacao_final) ? 'Sim' : 'Não';?></td>
				<td>
					<ul>
						<?php
						foreach ($avaliacao->obter_caminhos_modulos_moodle() as $modulo_com_caminho)
						{
							echo '<li>' . anchor_popup($avaliacao->obter_links_moodle_sem_icone($modulo_com_caminho->instance)[0], formatar_caminho($modulo_com_caminho->modulo_com_caminho)) . '</li>';
						}
						?>
					</ul>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
		<?php
		endif;
		if ($tipo_item === 'competencias'):
		?>
	<h2>Competências</h2>
	<table>
		<thead>
			<tr>
				<th>
				<?php
				echo form_checkbox(
					null,
					null,
					true
				);
				?>
				</th>
				<th>Ação</th>
				<th>Disciplina</th>
				<th>Código</th>
				<th>Nome</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($alteracoes as $index => $alteracao):
				$competencia = $alteracao['elemento'];
				$operacao = $alteracao['operacao'];
			?>
			<tr <?php echo ($operacao === 'manter') ? 'class="desmarcado"' : ''; ?>>
				<td>
					<?php
					if ($operacao !== 'manter')
					{
						echo form_checkbox(
							implode('-', array($tipo_item, $index)),
							$operacao,
							true,
							$alteracao['atributos']
						);
					}
					?>
				</td>
				<td class="<?php echo $operacao; ?>"><?php echo $alteracao['descricao']; ?></td>
				<td><?php echo $competencia->turma->disciplina->nome; ?></td>
				<td>
					<?php
					echo $competencia->codigo;
					if ($operacao !== 'cadastrar')
					{
						echo anchor_popup(
							site_url('cadastros/competencia/' . $competencia->turma->id . '/edit/' . $competencia->id),
							img('/assets/grocery_crud/themes/struct/css/images/ic_mode_edit_black_24px.svg'),
							array('title' => 'Abrir cadastro da competência')
						);
					}
					?>
				</td>
				<td><?php echo $competencia->nome; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
		endif;
	endforeach;

	echo $botao_voltar;
	echo form_submit(null, 'Aplicar ações selecionadas', 'class="btn btn-primary"');
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
