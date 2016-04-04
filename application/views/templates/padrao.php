    <div class="menu-superior">
        <label>Cadastros:</label>
        <?php echo anchor(site_url('cadastros/escola'), 'Escolas') ?> |
        <?php echo anchor(site_url('cadastros/formacao'), 'Formações') ?> |
        <?php echo anchor(site_url('cadastros/modalidade'), 'Modalidades') ?> |
        <?php echo anchor(site_url('cadastros/bloco'), 'Blocos') ?> |
        <?php echo anchor(site_url('cadastros/disciplina'), 'Disciplinas') ?> |
        <?php echo anchor(site_url('cadastros/turma'), 'Turmas') ?> |
        <?php echo anchor(site_url('cadastros/competencia'), 'Competências e subcompetências') ?> |
        Usuários |
        Perfis

        <br/>

        <label>Relatórios:</label>
        Mapa de competências |
        <?php echo anchor(site_url('relatorios/resultados_turma'), 'Demonstrativo de competências') ?> |
        Histórico individual |
        Resumo de conceitos individuais
    </div>

    <?php if (isset($output)) echo $output;
