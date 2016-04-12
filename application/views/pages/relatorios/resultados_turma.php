<?php if (isset($avaliacoes)): ?>
	<table class="relatorio_cabecalho_linha">
		<tr>
			<th>Curso:</th>
			<td><?php echo $disciplina_turma->turma->programa->nome; ?></td>
		</tr>
		<tr>
			<th>Trimestre:</th>
			<td><?php echo $disciplina_turma->obter_periodo(); ?></td>
		</tr>
		<tr>
			<th>Turma:</th>
			<td><?php echo $disciplina_turma->turma->nome; ?></td>
		</tr>
		<tr>
			<th>Bloco:</th>
			<td><?php echo $disciplina_turma->disciplina->bloco->nome; ?></td>
		</tr>
		<tr>
			<th>Disciplina:</th>
			<td><?php echo $disciplina_turma->disciplina->nome; ?></td>
		</tr>
	</table>

	<table class="relatorio">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th>Avaliação</th>
	<?php foreach($avaliacoes as $avaliacao): ?>
				<th colspan="<?php echo count($avaliacao->obter_subcompetencias()); ?>">
					<?php echo $avaliacao->nome . $avaliacao->obter_links_moodle()?>
				</th>
				<th></th>
	<?php endforeach; ?>
				<th colspan="<?php echo count($disciplina_turma->obter_subcompetencias()); ?>">Resultados por subcompetência</th>
				<th></th>
				<th colspan="<?php echo count($disciplina_turma->competencias) ?>">Resultados por competência</th>
				<th rowspan="4">Aprovação por aproveitamento na disciplina</th>
				<th rowspan="3" colspan="5">Rendimento para fins externos</th>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th>Competências</th>
	<?php foreach($avaliacoes as $avaliacao): ?>
		<?php foreach($avaliacao->competencias as $competencia): ?>
				<th
					colspan="<?php echo count($competencia->subcompetencias); ?>"
					title="<?php echo $competencia->nome; ?>"
				>
					<?php echo $competencia->codigo; ?>
				</th>
		<?php endforeach; ?>
				<th></th>
	<?php endforeach; ?>
	<?php foreach ($disciplina_turma->competencias as $competencia): ?>
				<th
					colspan="<?php echo count($competencia->subcompetencias); ?>"
					title="<?php echo $competencia->nome; ?>"
				>
					<?php echo $competencia->codigo; ?>
				</th>
	<?php endforeach ?>
				<th></th>
	<?php foreach ($disciplina_turma->competencias as $competencia): ?>
				<th rowspan="3" title="<?php echo $competencia->nome; ?>"><?php echo $competencia->codigo; ?></th>
	<?php endforeach ?>
			</tr>
			<tr>
				<th></th>
				<th></th>
				<th>Subcompetências</th>
	<?php foreach($avaliacoes as $avaliacao): ?>
		<?php foreach($avaliacao->obter_subcompetencias() as $subcompetencia): ?>
				<th title="<?php echo $subcompetencia->nome; ?>">
					<?php echo $subcompetencia->obter_codigo_sem_obrigatoriedade(); ?>
				</th>
		<?php endforeach; ?>
				<th></th>
	<?php endforeach; ?>
	<?php foreach ($disciplina_turma->obter_subcompetencias() as $subcompetencia): ?>
				<th title="<?php echo $subcompetencia->nome; ?>"><?php echo $subcompetencia->obter_codigo_sem_obrigatoriedade(); ?></th>
	<?php endforeach ?>
			</tr>
			<tr>
				<th>#</th>
				<th>Estudante</th>
				<th>Obrigatória?</th>
	<?php foreach($avaliacoes as $avaliacao): ?>
		<?php foreach($avaliacao->obter_subcompetencias() as $subcompetencia): ?>
				<th>
					<?php if($subcompetencia->obrigatoria) { echo SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE; } ?>
				</th>
		<?php endforeach; ?>
				<th></th>
	<?php endforeach; ?>
				<th></th>
		<?php foreach($disciplina_turma->obter_subcompetencias() as $subcompetencia): ?>
				<th>
					<?php if($subcompetencia->obrigatoria) { echo SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE; } ?>
				</th>
		<?php endforeach; ?>
				<th>ND</th>
				<th>D</th>
				<th>DL</th>
				<th>DML</th>
				<th>Grau</th>
			</tr>
		</thead>
		<tbody>
	<?php foreach($estudantes as $index=>$estudante): ?>
			<tr>
				<td><?php echo $index + 1; ?></td>
				<td colspan="2"><?php echo $estudante->nome_completo; ?></td>
		<?php foreach($avaliacoes as $avaliacao): ?>
			<?php foreach($avaliacao->obter_subcompetencias() as $subcompetencia): ?>
				<td>
					<?php
					if (isset($resultados_avaliacoes[$estudante->mdl_userid][$avaliacao->id][$subcompetencia->obter_codigo_sem_obrigatoriedade()]))
					{
						echo ($resultados_avaliacoes[$estudante->mdl_userid][$avaliacao->id][$subcompetencia->obter_codigo_sem_obrigatoriedade()]['demonstrada']) ? 'D' : 'ND';
					}
					?>
				</td>
			<?php endforeach; ?>
				<td></td>
		<?php endforeach; ?>
				<td></td>
	<?php foreach ($disciplina_turma->obter_subcompetencias() as $subcompetencia): ?>
				<td></td>
	<?php endforeach ?>
				<td></td>
	<?php foreach ($disciplina_turma->obter_subcompetencias() as $subcompetencia): ?>
				<td></td>
	<?php endforeach ?>
				<td></td>
	<?php foreach ($disciplina_turma->competencias as $competencia): ?>
				<td></td>
	<?php endforeach ?>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
	<?php endforeach; ?>
		</tbody>
	</table>

	<p>Relatório gerado em <?php echo date('d/m/Y H:i:s', time()); ?></p>
<?php endif; ?>
