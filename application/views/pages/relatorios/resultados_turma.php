<?php if (isset($dados_relatorio)): ?>
	<div class="relatorio">
		<table>
			<thead>
				<th>Estudante</th>
			</thead>
			<tbody>
<?php foreach($dados_relatorio['estudantes'] as $estudante): ?>
				<tr>
					<td><?php echo $estudante->nome_completo ?></td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
