<!-- dashboard -->
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel card card-lvl1" >
                <div class="panel-heading">Relatórios</div>
                <div class="panel-body">
                    <div class="col-md-3">
                        Mapa de Competências
                    </div>
                    <div class="col-md-3">
                        Demosntrativo de Competências
                    </div>
                    <div class="col-md-3">
                        Histórico Indiviudal
                    </div>
                    <div class="col-md-3">
                        Resumo de Conceitos Individuais
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel card card-lvl1">
                <div class="panel-heading">Cadastros</div>
                <div class="panel-body">
                    <div class="col-md-3">
                        <?php echo anchor(site_url('cadastros/escola'), 'Escolas'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo anchor(site_url('cadastros/formacao'), 'Formações'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo anchor(site_url('cadastros/modalidade'), 'Modalidades'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo anchor(site_url('cadastros/bloco'), 'Blocos'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo anchor(site_url('cadastros/disciplina'), 'Disciplinas'); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo anchor(site_url('cadastros/turma'), 'Turmas'); ?>
                    </div>
                    <div class="col-md-3">
                        Competências e subcompetências
                    </div>
                    <div class="col-md-3">
                        Usuários
                    </div>
                    <div class="col-md-3">
                        Perfis
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
