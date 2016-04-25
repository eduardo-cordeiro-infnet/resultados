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

	<div class="barra_rolagem_container">
		<div class="barra_rolagem_div_interna">
		</div>
	</div>
	<div class="relatorio_container">
	<table class="relatorio table table-condensed">
		<colgroup>
			<col></col>
		</colgroup>
	<?php
	foreach($avaliacoes as $avaliacao):
		if (count($avaliacao->obter_subcompetencias()) > 0):
	?>
		<colgroup span="<?php echo count($avaliacao->obter_subcompetencias()); ?>"></colgroup>
	<?php
		endif;
	endforeach;
	?>
		<colgroup span="<?php echo count($disciplina_turma->obter_subcompetencias()); ?>"></colgroup>
		<colgroup span="<?php echo count($disciplina_turma->competencias) ?>"></colgroup>
		<colgroup></colgroup>
		<colgroup></colgroup>
		<thead>
			<tr>
				<th>Avaliação</th>
	<?php
	foreach($avaliacoes as $avaliacao):
		if (count($avaliacao->obter_subcompetencias()) > 0):
	?>
				<th
					colspan="<?php echo count($avaliacao->obter_subcompetencias()); ?>"
					<?php if ($avaliacao->avaliacao_final) {echo 'class="av-final"';} ?>
				>
					<?php echo $avaliacao->nome . $avaliacao->obter_links_moodle(); ?>
				</th>
	<?php
		endif;
	endforeach;
	?>
				<th colspan="<?php echo count($disciplina_turma->obter_subcompetencias()); ?>">Resultados por subcompetência</th>
				<th
					colspan="<?php echo count($disciplina_turma->competencias) ?>"
					rowspan="2"
				>
					Resultados por competência
				</th>
				<th rowspan="3">Aprovação por aproveitamento na disciplina</th>
				<th rowspan="3">Grau (rendimento para fins externos)</th>
			</tr>
			<tr>
				<th>Competências</th>
	<?php
	foreach($avaliacoes as $avaliacao):
		if (count($avaliacao->obter_subcompetencias()) > 0):
	?>
			<?php foreach($avaliacao->competencias as $competencia): ?>
				<th
					colspan="<?php echo count($competencia->subcompetencias); ?>"
					title="<?php echo $competencia->nome; ?>"
					<?php if ($avaliacao->avaliacao_final) {echo 'class="av-final"';} ?>
				>
					<?php echo $competencia->codigo; ?>
				</th>
			<?php endforeach; ?>
	<?php
		endif;
	endforeach;
	?>
	<?php foreach ($disciplina_turma->competencias as $competencia): ?>
				<th
					colspan="<?php echo count($competencia->subcompetencias); ?>"
					title="<?php echo $competencia->nome; ?>"
				>
					<?php echo $competencia->codigo; ?>
				</th>
	<?php endforeach ?>
			</tr>
			<tr>
				<th>Subcompetências</th>
	<?php
	foreach($avaliacoes as $avaliacao):
		if (count($avaliacao->obter_subcompetencias()) > 0):
	?>
			<?php foreach($avaliacao->obter_subcompetencias() as $subcompetencia): ?>
				<th
					title="<?php echo $subcompetencia->nome; ?>"
					<?php if ($avaliacao->avaliacao_final) {echo 'class="av-final"';} ?>
				>
					<?php echo $subcompetencia->codigo_completo; ?>
				</th>
			<?php endforeach; ?>
	<?php
		endif;
	endforeach;
	?>
	<?php foreach ($disciplina_turma->obter_subcompetencias() as $subcompetencia): ?>
				<th title="<?php echo $subcompetencia->nome; ?>"><?php echo $subcompetencia->codigo_completo; ?></th>
	<?php endforeach ?>
	<?php foreach ($disciplina_turma->competencias as $competencia): ?>
				<th title="<?php echo $competencia->nome; ?>"><?php echo $competencia->codigo; ?></th>
	<?php endforeach ?>
				<!--
				<th></th>
				<th></th>
				-->
			</tr>
		</thead>
		<tbody>
	<?php foreach($estudantes as $index=>$estudante): ?>
			<tr>
				<td>
					<div class="numero_linha"><?php echo $index + 1; ?></div>
					<?php echo $estudante->nome_completo; ?>
				</td>
		<?php
		foreach($avaliacoes as $avaliacao):
			if (count($avaliacao->obter_subcompetencias()) > 0):
		?>
				<?php
				foreach($avaliacao->obter_subcompetencias() as $subcompetencia):
					$demonstrada = (
						isset($resultados_avaliacoes[$estudante->mdl_userid][$avaliacao->id][$subcompetencia->obter_codigo_sem_obrigatoriedade()])
						&& $resultados_avaliacoes[$estudante->mdl_userid][$avaliacao->id][$subcompetencia->obter_codigo_sem_obrigatoriedade()]['demonstrada']
					);
				?>
				<td
					class="<?php if (!$demonstrada) {echo 'nao_';} ?>demonstrada"
					title="<?php echo $avaliacao->nome . ' / ' . $subcompetencia->codigo_completo; ?>"
				>
					<?php
					if (isset($resultados_avaliacoes[$estudante->mdl_userid][$avaliacao->id][$subcompetencia->obter_codigo_sem_obrigatoriedade()]))
					{
						if ($subcompetencia->obrigatoria)
						{
							echo SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE;
						}
						echo ($demonstrada) ? 'D' : 'ND';
					}
					?>
				</td>
				<?php endforeach; ?>
		<?php
			endif;
		endforeach;
		?>
		<?php
		foreach ($disciplina_turma->obter_subcompetencias() as $subcompetencia):
			$demonstrada = (
				isset($resultados_gerais[$estudante->mdl_userid][$subcompetencia->obter_codigo_competencia()][$subcompetencia->obter_codigo_sem_obrigatoriedade()])
				&& $resultados_gerais[$estudante->mdl_userid][$subcompetencia->obter_codigo_competencia()][$subcompetencia->obter_codigo_sem_obrigatoriedade()]['demonstrada']
			);
		?>
				<td
					class="bold <?php if (!$demonstrada) {echo 'nao_';} ?>demonstrada"
					title="<?php echo 'Resultados por subcompetência / ' . $subcompetencia->codigo_completo; ?>"
				>
					<?php
					if (isset($resultados_gerais[$estudante->mdl_userid]))
					{
						if ($subcompetencia->obrigatoria)
						{
							echo SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE;
						}
						echo ($demonstrada) ? 'D' : 'ND';
					}
					?>
				</td>
		<?php endforeach; ?>
		<?php
		foreach ($disciplina_turma->competencias as $competencia):
			$demonstrada = (
				isset($resultados_gerais[$estudante->mdl_userid][$competencia->codigo])
				&& $resultados_gerais[$estudante->mdl_userid][$competencia->codigo]['resultado'] !== 'ND'
			);
		?>
				<td
					class="bold <?php if (!$demonstrada) {echo 'nao_';} ?>demonstrada"
					title="<?php echo 'Resultados por competência / ' . $competencia->codigo; ?>"
				>
					<?php
					if (isset($resultados_gerais[$estudante->mdl_userid][$competencia->codigo]))
					{
						echo $resultados_gerais[$estudante->mdl_userid][$competencia->codigo]['resultado'];
					}
					?>
				</td>
		<?php
		endforeach;

		$aprovacao = (
			isset($resultados_gerais[$estudante->mdl_userid])
			&& $resultados_gerais[$estudante->mdl_userid]['aprovacao']
		);
		?>
				<td
					class="bold <?php if (!$aprovacao) {echo 'nao_';} ?>aprovacao"
					title="Aprovação por aproveitamento na disciplina"
				>
					<?php
					if (isset($resultados_gerais[$estudante->mdl_userid]))
					{
						if ($disciplina_turma->avaliacao_final_inexistente)
						{
							echo 'N/D';
						}
						else
						{
							echo ($aprovacao) ? 'Sim' : 'Não';
						}
					}
					?>
				</td>
				<td
					class="bold <?php if (!isset($resultados_gerais[$estudante->mdl_userid]) || $resultados_gerais[$estudante->mdl_userid]['grau'] < 7) {echo 'nao_';} ?>aprovacao"
					title="Grau (rendimento para fins externos)"
				>
					<?php
					if (isset($resultados_gerais[$estudante->mdl_userid]))
					{
						if ($disciplina_turma->avaliacao_final_inexistente)
						{
							echo 'N/D';
						}
						else
						{
							echo number_format($resultados_gerais[$estudante->mdl_userid]['grau'], 2, ',', null);
						}
					}
					else
					{
						echo nbs();
					}
					?>
				</td>
			</tr>
	<?php endforeach; ?>
		</tbody>
	</table>
	</div>
	<p>Relatório gerado em <?php echo date('d/m/Y H:i:s', time()); ?></p>
<?php endif; ?>
