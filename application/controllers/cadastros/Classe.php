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

	function _output_padrao($output = null, $view_principal = 'templates/cadastros')
	{
		$this->load->view('templates/cabecalho', $output);
		$this->load->view('templates/menu', $output);
		$this->load->view('templates/navbar', $output);
		$this->load->view($view_principal, $output);
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

			->add_action('Cadastrar turmas', base_url('assets/img/ic_group_add_black_24px.svg'), 'cadastros/classe/turmas')
			->add_action('Preparar estrutura da classe', base_url('assets/img/ic_build_black_24px.svg'), 'cadastros/classe/preparar_estrutura')

			->unset_read()
		;

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de classes';
		$output->mensagem_informativa = 'Nesta tela são cadastradas as classes de cada programa.</p><p>
			Cada classe deve ser associada a um programa e modalidade.</p><p>
			O período da classe indica o trimestre e ano em que a classe foi iniciada. Este dado é apenas para informação, não impacta nas funcionalidades do sistema.</p><p>
			Se for definida a categoria da classe no Moodle (ou seja, a página do Moodle correspondente à classe), é exibido um link para o Moodle na lista.</p><p>
			É possível cadastrar as turmas de cada classe clicando no ícone "<i>Cadastrar turmas</i>", na coluna "Ações": ' . img(base_url('assets/img/livros-vertical.png'), '', array('title' => 'Cadastrar turmas')) . '</p><p>
			Diretamento na tela de edição de cada classe, é possível fazer um "pré-cadastro" de disciplinas cursadas, com opções menos detalhadas que o ícone descrito acima.'
		;

		$this->_output_padrao($output);
	}

	/**
	 * Preencher estrutura
	 *
	 * Preenche as alterações necessárias no banco de dados para inserir
	 * a estrutura de disciplinas, avaliações e competências de cada turma da classe
	 */
	public function preparar_estrutura($id_classe)
	{
		$this->load
			->library(array(
				'Geracao_estrutura',
				'session'
			))
			->helper('form')
			->model('Classe_model')
		;

		$alteracoes_estrutura = $this->geracao_estrutura->obter_estrutura_classe($id_classe);

		if (isset($alteracoes_estrutura))
		{
			$output = new stdClass();
			$output->css_files = array(
				base_url('assets/css/preparar_estrutura.css'),
				base_url('assets/grocery_crud/themes/struct/css/struct.css')
			);

			$output->title = 'Preparar estrutura de classe';
			$output->fechamento_body = "
		<script>
		$(function() {
			PrepararEstrutura.registrarListeners();
		});
		</script>
			";

			$output->alteracoes_estrutura = $alteracoes_estrutura;

			// Grava as alterações em uma variável de sessão, para poderem ser acessadas após submeter o formulário
			$this->session->alteracoes_estrutura = $output->alteracoes_estrutura;

			$this->_output_padrao($output, 'pages/preparar_estrutura');
		}
		else
		{
			redirect('cadastros/classe');
		}
	}

	public function atualizar_estrutura($id_classe = null)
	{
		$db = $this->db;

		$this->load
			->helper(array(
				'obj_array'
			))
			->model(array(
				'Turma_model',
				'Avaliacao_model',
				'Competencia_model'
			))
			// Sessão deve ser carregada após modelos para as classes serem incluídas antes das instâncias serem desserializadas
			->library('session')
		;
		$this->output->enable_profiler(TRUE);

		$alteracoes_estrutura = $this->session->alteracoes_estrutura;
		$alteracoes_selecionadas = $this->input->post();

		$itens_remover = array();
		$itens_atualizar = array();
		$itens_cadastrar = array();

		foreach ($alteracoes_selecionadas as $id_chk => $operacao) {
			$array_chk = explode('-', $id_chk);
			$tipo_item = $array_chk[0];
			$index = $array_chk[1];
			$alteracao = $alteracoes_estrutura[$tipo_item][$index];

			/*
			// Tentativa de validar no back-end, não funcionando
			if (isset($alteracao['dependencia']) && !isset($alteracoes_selecionadas[$alteracao['dependencia']]))
			{
				$this->preparar_estrutura($id_classe, 'Não foi possível gravar a seguinte alteração, pois uma alteração da qual ela depende não foi selecionada:<br />' + (string) $alteracao->elemento);
			}
			*/

			if ($operacao === 'remover')
			{
				$itens_remover[$tipo_item][] = $alteracao['elemento']->id;
			}
			else if ($operacao === 'atualizar')
			{
				$itens_atualizar[$tipo_item][] = $alteracao['array_para_base'];

				if (isset($alteracao['relacionamentos_n_n']))
				{
					foreach ($alteracao['relacionamentos_n_n'] as $tabela => $alteracoes_n_n)
					{
						if (!empty($alteracoes_n_n['cadastrar']))
						{
							$itens_cadastrar[$tabela][] = array(
								$alteracao['elemento']->id,
								$alteracoes_n_n['cadastrar']
							);
						}

						if (!empty($alteracoes_n_n['remover']))
						{
							$itens_remover[$tabela][] = array(
								$alteracao['elemento']->id,
								$alteracoes_n_n['remover']
							);
						}
					}
				}
			}
			else if ($operacao === 'cadastrar')
			{
				$itens_cadastrar[$tipo_item][$index] = $alteracao;
			}
		}

		if (!empty($itens_remover['avaliacoes_mdl_course_modules']))
		{
			foreach ($itens_remover['avaliacoes_mdl_course_modules'] as $dados)
			{
				$db->where('id_avaliacao', $dados[0])->where_in('instance_mdl_course_modules', $dados[1])->delete('avaliacoes_mdl_course_modules');
			}
		}

		if (!empty($itens_remover['avaliacoes']))
		{
			$db->where_in('id_avaliacao', $itens_remover['avaliacoes'])->delete('avaliacoes_mdl_course_modules');
			$db->where_in('id', $itens_remover['avaliacoes'])->delete('avaliacoes');
		}

		if (!empty($itens_remover['turmas']))
		{
			$db->where_in('id', $itens_remover['turmas'])->delete('turmas');
		}

		if (!empty($itens_remover['competencias']))
		{
			$db->where_in('id', $itens_remover['competencias'])->delete('competencias');
		}

		foreach ($itens_atualizar as $tipo_item => $dados)
		{
			$db->update_batch($tipo_item, $dados, 'id');
		}

		if (!empty($itens_cadastrar['turmas']))
		{
			foreach ($itens_cadastrar['turmas'] as $index => $alteracao)
			{
				$db->insert('turmas', $alteracao['array_para_base']);
				$alteracoes_estrutura['turmas'][$index]['elemento']->id = $db->insert_id();
			}
		}

		if (!empty($itens_cadastrar['avaliacoes']))
		{
			foreach ($itens_cadastrar['avaliacoes'] as $index => $alteracao)
			{
				$alteracao['array_para_base']['id_turma'] = $alteracao['elemento']->turma->id;
				$db->insert('avaliacoes', $alteracao['array_para_base']);

				$id_avaliacao = $db->insert_id();
				$alteracoes_estrutura['avaliacoes'][$index]['elemento']->id = $id_avaliacao;

				if (!empty($alteracao['relacionamentos_n_n']['avaliacoes_mdl_course_modules']['cadastrar']))
				{
					$itens_cadastrar['avaliacoes_mdl_course_modules'][] = array(
						$id_avaliacao,
						$alteracao['relacionamentos_n_n']['avaliacoes_mdl_course_modules']['cadastrar']
					);
				}
			}
		}

		if (!empty($itens_cadastrar['competencias']))
		{
			foreach ($itens_cadastrar['competencias'] as $index => $alteracao)
			{
				$db->insert('competencias', $alteracao['array_para_base']);
				$alteracoes_estrutura['competencias'][$index]['elemento']->id = $db->insert_id();
			}
		}

		if (!empty($itens_cadastrar['avaliacoes_mdl_course_modules']))
		{
			foreach ($itens_cadastrar['avaliacoes_mdl_course_modules'] as $itens)
			{
				$dados_insert_n_n = array();

				foreach ($itens[1] as $instance)
				{
					$dados_insert_n_n[] = array(
						'id_avaliacao' => $itens[0],
						'instance_mdl_course_modules' => $instance
					);
				}

				$db->insert_batch('avaliacoes_mdl_course_modules', $dados_insert_n_n);
			}
		}

		redirect(str_replace('atualizar_estrutura', 'success', uri_string()));
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

			->add_action('Cadastrar avaliações', base_url('assets/img/ic_note_add_black_24px.svg'), 'cadastros/classe/avaliacoes')
			->add_action('Cadastrar competências', base_url('assets/img/ic_add_circle_outline_black_24px.svg'), 'cadastros/competencia')

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

			->add_action('Associar competências a rubricas', base_url('assets/img/ic_add_circle_link_black_24px.svg'), 'cadastros/classe/rubricas')

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
