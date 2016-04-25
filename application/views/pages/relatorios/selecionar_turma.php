<?php if (!empty($disciplinas_turmas)) : ?>
	<?php echo form_open('relatorios/resultados_turma/relatorio'); ?>
		<?php echo form_label('Selecione uma turma:', 'id_disciplina_turma'); ?>
		<br />
		<?php echo form_dropdown('id_disciplina_turma', $disciplinas_turmas, null, array('id' => 'id_disciplina_turma')); ?>
		<br />
		<?php echo form_submit('', 'Exibir resultados'); ?>
	<?php echo form_close(); ?>
<?php else : ?>
	<p>
		Não há nenhuma disciplina com rubricas associadas a subcompetências.
		<?php echo anchor(site_url('cadastros/turma'), 'Acesse o cadastro de turmas') ?>
		para definir disciplinas, avaliações, rubricas e subcompetências.
	</p>
<?php endif; ?>
