<form class="alteracoes-estrutura">
<?php
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
			<?php foreach ($alteracoes as $alteracao): ?>
			<tr class="<?php echo $alteracao['operacao']; ?>">
				<td>
				<?php if ($alteracao['operacao'] !== 'manter'): ?>
					<input
						type="checkbox"
						name="turma-<?php echo $alteracao['elemento']->disciplina->id; ?>"
						checked="true"
					/>
				<?php endif; ?>
				</td>
				<td><?php echo $alteracao['descricao']; ?></td>
				<td><?php echo $alteracao['elemento']->disciplina->bloco->nome; ?></td>
				<td><?php echo $alteracao['elemento']->disciplina->denominacao_bloco; ?></td>
				<td>
					<?php echo $alteracao['elemento']->disciplina->nome . (($alteracao['operacao'] !== 'cadastrar') ? anchor_popup(site_url('cadastros/classe/turmas/' . $alteracao['elemento']->classe->id . '/edit/' . $alteracao['elemento']->id), img('/assets/grocery_crud/themes/struct/css/images/ic_mode_edit_black_24px.svg'), array('title' => 'Abrir cadastro da turma')) : nbs()); ?>
				</td>
				<td>
					<?php
						echo (isset($alteracao['elemento']->id_mdl_course)) ? anchor($alteracao['link_moodle'], $alteracao['caminho_curso_moodle']) : nbs();
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php
		endif;
	endforeach;
?>
</form>
