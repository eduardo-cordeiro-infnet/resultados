<nav id="menu">
  <ul>
    <li><?php echo anchor('/', 'Dashboard'); ?></li>
    <li class="divider">Relatórios</li>
    <li><?php echo anchor('#', 'Mapa de Competências'); ?></li>
    <li><?php echo anchor(site_url('relatorios/resultados_turma'), 'Demonstrativo de Competências'); ?></li>
    <li><?php echo anchor('#', 'Histórico Indiviudal'); ?></li>
    <li><?php echo anchor('#', 'Resumo de Conceitos Individuais'); ?></li>
    <li class="divider">Cadastro</li>
    <li><?php echo anchor(site_url('cadastros/escola'), 'Escolas'); ?></li>
    <li><?php echo anchor(site_url('cadastros/modalidade'), 'Modalidades'); ?></li>
    <li><?php echo anchor(site_url('cadastros/programa'), 'Programas'); ?></li>
    <li><?php echo anchor(site_url('cadastros/bloco'), 'Blocos'); ?></li>
    <li><?php echo anchor(site_url('cadastros/disciplina'), 'Disciplinas'); ?></li>
    <li><?php echo anchor(site_url('cadastros/classe'), 'Classes'); ?></li>
    <li><?php echo anchor(site_url('cadastros/competencia'), 'Competências'); ?></li>
    <li><?php echo  anchor('#', 'Usuários'); ?></li>
    <li><?php echo anchor('#', 'Perfis'); ?></li>
  </ul>
</nav>
