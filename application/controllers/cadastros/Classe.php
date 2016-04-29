<?php
class Classe extends CI_Controller {

	public $escolas = array();

	public function __construct()
	{
			parent::__construct();

			$this->load
				->library('grocery_CRUD')
				->database()
			;
	}

	function _output_padrao($output = null)
	{
		$this->load->view('templates/cabecalho', $output);
		$this->load->view('templates/menu', $output);
		$this->load->view('templates/navbar', $output);
		//$this->load->view('templates/padrao', $output);
		$this->load->view('templates/cadastros', $output);
		$this->load->view('templates/rodape', $output);
	}

	public function cadastro()
	{
		$crud = new grocery_CRUD();

		$crud->set_model('grocery_crud/Classe_crud_model');

		$crud->set_subject('classe')
			->set_table('classes')

			->columns('id_escola_red', 'id_programa', 'nome', 'id_modalidade', 'periodo', 'link_moodle', 'qtd_disciplinas_calc', 'ativa')
			->fields('nome', 'id_programa', 'id_modalidade', 'id_mdl_course_category', 'trimestre', 'ano', 'ativa')

			->set_relation('id_escola_red', 'escolas', '{sigla}')
			->set_relation('id_programa', 'programas', '{sigla} - {nome}')
			->set_relation('id_modalidade', 'modalidades', '{nome}')

			->field_type('trimestre', 'dropdown', array(1 => '1T', 2 => '2T', 3 => '3T', 4 => '4T'))
			->field_type('ano', 'enum', array(2012, 2013, 2014, 2015, 2016, 2017, 2018))
			->field_type('ativa', 'dropdown', array('Não', 'Sim'))
			->field_type('id_mdl_course_category', 'dropdown', $this->Classe_crud_model->obter_categorias_moodle())

			->required_fields('nome', 'id_programa', 'id_modalidade')

			->display_as('id_escola_red', 'Escola')
			->display_as('id_programa', 'Programa')
			->display_as('id_modalidade', 'Modalidade')
			->display_as('qtd_disciplinas_calc', 'Disciplinas')
			->display_as('id_mdl_course_category', 'Categoria no Moodle')
			->display_as('periodo', 'Período')
			->display_as('link_moodle', 'Acessar Moodle')

			->callback_column('periodo', array($this->Classe_crud_model, 'obter_periodo_classe'))
			->callback_column('link_moodle', array($this->Classe_crud_model, 'obter_link_moodle'))

			->add_action('Cadastrar turmas da classe', base_url('assets/img/livros-vertical.png'), 'cadastros/classe/turmas')

			->unset_read()
		;

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de classes';
		$output->mensagem_informativa = 'Nesta tela são cadastradas as classes de cada programa.</p><p>
			Cada classe deve ser associada a um programa e modalidade.</p><p>
			O período da classe indica o trimestre e ano em que a classe foi iniciada. Este dado é apenas para informação, não impacta nas funcionalidades do sistema.</p><p>
			Se for definida a categoria da classe no Moodle (ou seja, a página do Moodle correspondente à classe), é exibido um link para o Moodle na lista.</p><p>
			É possível cadastrar as disciplinas cursadas por cada classe clicando no ícone "<i>Cadastrar disciplinas da classe</i>", na coluna "Ações": ' . img(base_url('assets/img/livros-vertical.png'), '', array('title' => 'Cadastrar disciplinas da classe')) . '</p><p>
			Diretamento na tela de edição de cada classe, é possível fazer um "pré-cadastro" de disciplinas cursadas, com opções menos detalhadas que o ícone descrito acima.'
		;

		$this->_output_padrao($output);
	}

	public function turmas($id_classe = null)
	{
		$crud = new grocery_CRUD();

		$crud->set_model('grocery_crud/Classe_crud_model');

		$crud->set_subject('turma')
			->set_table('turmas')

			->columns('id_bloco_red', 'id_disciplina', 'link_moodle', 'periodo')
			->fields('id_classe', 'id_disciplina', 'id_mdl_course', 'trimestre_inicio', 'ano_inicio', 'trimestre_fim', 'ano_fim')
			->unset_edit_fields('id_classe')

			->set_relation('id_bloco_red', 'blocos', '{nome}')

			->field_type('id_classe', 'hidden', $id_classe)
			->field_type('id_disciplina', 'dropdown', $this->Classe_crud_model->obter_disciplinas_blocos($id_classe, $crud->getState(), $crud->getStateInfo()))
			->field_type('id_mdl_course', 'dropdown', $this->Classe_crud_model->obter_cursos_moodle($id_classe))
			->field_type('trimestre_inicio', 'dropdown', array(1 => '1T', 2 => '2T', 3 => '3T', 4 => '4T'))
			->field_type('ano_inicio', 'enum', array(2014, 2015, 2016, 2017, 2018))
			->field_type('trimestre_fim', 'dropdown', array(1 => '1T', 2 => '2T', 3 => '3T', 4 => '4T'))
			->field_type('ano_fim', 'enum', array(2014, 2015, 2016, 2017, 2018))

			->callback_column('link_moodle', array($this->Classe_crud_model, 'obter_link_turma_moodle'))
			->callback_column('periodo', array($this->Classe_crud_model, 'obter_periodo_disciplina'))

			->required_fields('id_disciplina')

			->display_as('id_bloco_red', 'Bloco')
			->display_as('id_disciplina', 'Disciplina')
			->display_as('link_moodle', 'Acessar Moodle')
			->display_as('periodo', 'Período')
			->display_as('id_mdl_course', 'Turma no Moodle')
			->display_as('trimestre_inicio', 'Trimestre inicial')
			->display_as('ano_inicio', 'Ano inicial')
			->display_as('trimestre_fim', 'Trimestre final')
			->display_as('ano_fim', 'Ano final')

			->add_action('Cadastrar avaliações', base_url('assets/img/texto-lapis.png'), 'cadastros/classe/avaliacoes')
			->add_action('Cadastrar competências', base_url('assets/img/lista-num.png'), 'cadastros/competencia')

			->unset_read()
		;

		if (intval($id_classe) > 0)
		{
			$crud->where('id_classe', $id_classe);
		}

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de turmas';
		$output->mensagem_informativa = 'Nesta tela são cadastradas as turmas cada classe.</p><p>
			Se for definida a turma no Moodle (ou seja, a página do Moodle correspondente à turma), é exibido um link para o Moodle na lista.</p><p>
			É necessário definir a disciplina no Moodle para que as avaliações e suas rubricas possam ser associadas a competências.</p><p>
			Caso a disciplina tenha duração de apenas 1 ciclo, não é necessário definir trimestre e ano finais, apenas iniciais.</p><p>
			É possível cadastrar as competências de cada disciplina clicando no ícone "<i>Cadastrar competências</i>" ' . img(base_url('assets/img/lista-num.png'), '', array('title' => 'Cadastrar competências')) . ', na coluna "Ações".</p><p>
			É possível cadastrar as avaliações de cada disciplina clicando no ícone "<i>Cadastrar avaliações</i>" ' . img(base_url('assets/img/texto-lapis.png'), '', array('title' => 'Cadastrar avaliações')) . ', na coluna "Ações".'
		;

		if (false && $id_classe && $crud->getState() === 'list')
		{
			$this->load->helper('simple_html_dom');
			$html = str_get_html($output->output);
			$botao_adicionar_disciplina = $html->find('.tDiv2', 0);
			$botao_adicionar_bloco = clone $botao_adicionar_disciplina;
			$botao_adicionar_disciplina->find('a', 0)->href;
		}

		$this->_output_padrao($output);
	}

	public function avaliacoes($id_turma = null)
	{
		$crud = new grocery_CRUD();

		$crud->set_model('grocery_crud/Classe_crud_model');

		$crud->set_subject('avaliação')
			->set_table('avaliacoes')

			->columns('id_turma', 'nome', 'links_moodle', 'avaliacao_final', 'ativa')
			->fields('id_turma', 'nome', 'avaliacao_final', 'ativa', 'atividades_moodle')

			->set_relation_n_n(
				'atividades_moodle',
				'avaliacoes_mdl_course_modules',
				'lmsinfne_mdl.mdl_assign',
				'id_avaliacao',
				'instance_mdl_course_modules',
				'name',
				'',
				array('course' => $this->Classe_crud_model->obter_id_curso_moodle($id_turma))
			)

			->field_type('id_turma', 'dropdown', $this->Classe_crud_model->obter_turmas($id_turma))
			->field_type('avaliacao_final', 'dropdown', array('Não', 'Sim'))
			->field_type('ativa', 'dropdown', array('Não', 'Sim'))

			->callback_column('links_moodle', array($this->Classe_crud_model, 'obter_links_avaliacoes_moodle'))

			->required_fields('id_turma', 'nome')

			->display_as('id_turma', 'Disciplina')
			->display_as('links_moodle', 'Acessar Moodle')
			->display_as('avaliacao_final', 'Avaliação final')
			->display_as('atividades_moodle', 'Atividades no Moodle')

			->add_action('Associar competências a rubricas', base_url('assets/img/prancheta-correto.png'), 'cadastros/classe/rubricas')

			->unset_read()
		;

		if (intval($id_turma) > 0)
		{
			$crud->where('id_turma', $id_turma);
		}

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de avaliações da disciplina';

		$output->mensagem_informativa = implode('</p><p>', array(
			'Nesta tela são cadastradas as avaliações de cada disciplina.',
			'Se for associada uma ou mais atividades no Moodle à avaliação (ou seja, a página do Moodle correspondente à tarefa), é exibido um link para o Moodle na lista.',
			'Caso seja associada mais de uma tarefa do Moodle, o resultado será compilado considerando todas as rubricas das tarefas como se estivessem em uma única avaliação. Por exemplo, se o TP1 estiver dividido no Moodle em "Parte 1" e "Parte 2", basta criar um registro nesta tela chamado "TP1", associando as duas tarefas à mesma avaliação.',
			'É necessário associar pelo menos uma tarefa do Moodle para que as suas rubricas possam ser associadas a competências.',
			'É possível associar as competências da disciplina a cada avaliação clicando no ícone "<em>Associar competências a rubricas</em>", na coluna "Ações": ' . img(base_url('assets/img/prancheta-correto.png'), '', array('title' => 'Associar competências a rubricas')),
			'Se uma avaliação estiver definida como "Avaliação final", qualquer subcompetência que seja avaliada como "Não Demonstrada" nesta avaliação ficará automaticamente como "ND" na subcompetência da disciplina em geral, independente das demais avaliações.'
		));

		$this->_output_padrao($output);
	}

	public function rubricas($id_avaliacao = null)
	{
		$crud = new grocery_CRUD();

		$crud->set_model('grocery_crud/Classe_crud_model');

		$id_turma = $this->Classe_crud_model->obter_id_turma($id_avaliacao);

		$crud->set_subject('rubrica')
			->set_table('v_rubricas_avaliacoes')

			->set_primary_key('id_mdl_gradingform_rubric_criteria')

			->columns('id_turma', 'id_avaliacao', 'rubrica', 'subcompetencias')
			->fields('id_turma', 'id_avaliacao', 'rubrica', 'subcompetencias')

			->set_relation_n_n(
				'subcompetencias',
				'subcompetencias_mdl_gradingform_rubric_criteria',
				'subcompetencias',
				'id_mdl_gradingform_rubric_criteria',
				'id_subcompetencia',
				'{codigo_completo_calc} {nome}',
				'',
				array('id_turma_red' => $id_turma)
			)

			->field_type('rubrica', 'readonly')

			->callback_column('id_turma', array($this->Classe_crud_model, 'obter_caminho_turma'))
			->callback_edit_field('id_turma', array($this->Classe_crud_model, 'obter_caminho_turma'))
			->callback_column('id_avaliacao', array($this->Classe_crud_model, 'obter_nome_avaliacao'))
			->callback_edit_field('id_avaliacao', array($this->Classe_crud_model, 'obter_nome_avaliacao'))

			->display_as('id_turma', 'Disciplina')
			->display_as('id_avaliacao', 'Avaliação')
			->display_as('subcompetencias', 'Subcompetências')

			->unset_add()
			->unset_delete()
			->unset_read()

			->callback_update(array($this->Classe_crud_model, 'salvar_rubricas_subcompetencias'))
		;

		if (intval($id_avaliacao) > 0)
		{
			$crud->where('id_avaliacao', $id_avaliacao);
		}

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de subcompetências por rubrica';
		$output->mensagem_informativa = 'Nesta tela são associadas as rubricas do Moodle às competências definidas no ' . anchor(site_url('cadastros/competencia'), 'cadastro de competências') . '.</p><p>
			As rubricas são importadas diretamente das tarefas do Moodle.</p><p>
			Cada rubrica pode ser associada a uma ou mais subcompetências.'
		;

		$this->_output_padrao($output);
	}

}