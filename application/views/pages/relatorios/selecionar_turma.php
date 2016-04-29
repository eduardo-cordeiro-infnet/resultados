<?php if (!empty($turmas)) : ?>
	<?php echo form_open('relatorios/resultados_turma/relatorio'); ?>
		<?php echo form_label('Selecione uma turma:', 'id_turma'); ?>
		<br />
		<?php echo form_dropdown('id_turma', $turmas, null, array('id' => 'id_turma')); ?>
		<br />
		<?php echo form_submit('', 'Exibir resultados'); ?>
	<?php echo form_close(); ?>
<?php else : ?>
	<p>
		Não há nenhuma classe com rubricas associadas a subcompetências.
		<?php echo anchor(site_url('cadastros/classe'), 'Acesse o cadastro de classes') ?>
		para definir disciplinas, avaliações, rubricas e subcompetências.
	</p>
<?php endif; ?>
