<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Geracao_estrutura {
	private $CI;

	// Elemento utilizado para gerar a estrutura, definido nesta instância
	// para poder imprimir os dados do elemento na preparação de estrutura
	public $elemento_principal;

	// Lista de alterações da estrutura, definida como propriedade da instância
	// para executar _preparar_alteracoes mais de uma vez usando o mesmo objeto
	private $alteracoes_estrutura = array();

	// Lista de comandos de alterações para padronizar as diferenças de registros
	private $operacoes_descricoes = array(
		'manter' => 'Nenhuma',
		'atualizar' => 'Atualizar campo',
		'cadastrar' => 'Cadastrar',
		'remover' => 'Excluir'
	);

	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->database();
	}

	/**
	 * Obter estrutura de classe
	 *
	 * Retorna as alterações necessárias para gerar
	 * a estrutura de subcadastros de uma classe
	 * @return string
	 */
	public function obter_estrutura_classe($id_classe)
	{
		$CI = $this->CI;
		$CI->load->helper('class');
		carregar_classe('models/Classe_model');

		$classe = $CI->db->get_where('classes', array('id' => $id_classe))->custom_row_object(0, 'Classe_model');

		if (!isset($classe))
		{
			return null;
		}

		$this->elemento_principal = $classe;
		$this->_preparar_alteracoes($classe);

		foreach ($this->alteracoes_estrutura['turmas'] as $alteracao)
		{
			$this->_preparar_alteracoes($alteracao['elemento']);
		}

		foreach ($this->alteracoes_estrutura['competencias'] as $alteracao)
		{
			$this->_preparar_alteracoes($alteracao['elemento']);
		}

		$this->_organizar_alteracoes();

		return $this->alteracoes_estrutura;
	}

	/**
	 * Preparar alterações
	 *
	 * Define as alterações necessárias para gerar a estrutura de cadastros,
	 * a partir do registro informado e atribui à propriedade $alteracoes_estrutura
	 */
	protected function _preparar_alteracoes($registro)
	{
		$CI = $this->CI;
		$CI->load
			->helper(array(
				//'class',
				'obj_array',
				'format'
			))
			->library('Consultas_SQL')
		;

		$classe_registro = get_class($registro);
		$registro->popular(true);

		if ($classe_registro === 'Classe_model')
		{
			if (!isset($this->alteracoes_estrutura['turmas']))
			{
				$this->alteracoes_estrutura['turmas'] = array();
			}

			$this->alteracoes_estrutura['turmas'] = array_merge($this->alteracoes_estrutura['turmas'], $this->_obter_alteracoes_turmas($registro));
		}
		else if ($classe_registro === 'Turma_model')
		{
			if (!isset($this->alteracoes_estrutura['avaliacoes']))
			{
				$this->alteracoes_estrutura['avaliacoes'] = array();
			}

			$this->alteracoes_estrutura['avaliacoes'] = array_merge($this->alteracoes_estrutura['avaliacoes'], $this->_obter_alteracoes_avaliacoes($registro));

			if (!isset($this->alteracoes_estrutura['competencias']))
			{
				$this->alteracoes_estrutura['competencias'] = array();
			}

			$this->alteracoes_estrutura['competencias'] = array_merge($this->alteracoes_estrutura['competencias'], $this->_obter_alteracoes_competencias($registro));

			if (!isset($this->alteracoes_estrutura['subcompetencias']))
			{
				$this->alteracoes_estrutura['subcompetencias'] = array();
			}

			$this->alteracoes_estrutura['subcompetencias'] = array_merge($this->alteracoes_estrutura['subcompetencias'], $this->_obter_alteracoes_subcompetencias($registro));
		}
	}

	/**
	 * Obter alterações de turmas
	 *
	 * Retorna alterações na estrutura de turmas da classe
	 * @return array
	 */
	protected function _obter_alteracoes_turmas($classe)
	{
		$CI = $this->CI;
		$alteracoes_turmas = array();

		carregar_classe('models/Disciplina_model');
		// Listar todas as disciplinas que fazem parte dos blocos do programa da classe
		$disciplinas = $CI->db
			->select('d.*')
			->join('programas_blocos pb', 'pb.id_bloco = d.id_bloco')
			->join('classes c', 'c.id_programa = pb.id_programa')
			->where('c.id', $classe->id)
			->get('disciplinas d')
			->custom_result_object('Disciplina_model')
		;

		foreach ($disciplinas as $disciplina)
		{
			// Preenche os dados da instância da disciplina
			$disciplina->popular(true);

			$id_mdl_course = null;
			$caminho_curso_moodle = null;
			$descricao = null;

			/*
			var_dump($CI->consultas_sql->mdl_curso_com_caminho_mdl_categoria(true));
			die();
			*/

			$curso_moodle = $CI->db->query(
				$CI->consultas_sql->mdl_curso_com_caminho_mdl_categoria(true),
				array($classe->id, $disciplina->nome, $disciplina->nome)
			)->row();
			if (isset($curso_moodle))
			{
				$id_mdl_course = $curso_moodle->id;
				$caminho_curso_moodle = formatar_caminho($curso_moodle->curso_com_caminho);
			}

			// Criar elemento que servirá como padrão para comparar com as turmas existentes
			carregar_classe('models/Turma_model');
			$elemento = new Turma_model(array(
				'disciplina' => $disciplina,
				'classe' => $classe,
				'id_mdl_course' => $id_mdl_course
			));
			$elemento->popular(true);

			$turma_disciplina = null;

			// Obter a primeira turma cadastrada para a disciplina na classe
			foreach ($classe->turmas as $turma) {
				if ($turma->disciplina->id === $disciplina->id)
				{
					$turma_disciplina = $turma;
					break;
				}
			}

			if (isset($turma_disciplina))
			{
				// Se houver uma turma para a disciplina, verificar se o curso Moodle está correto
				if ($turma_disciplina->id_mdl_course === $id_mdl_course)
				{
					// Definir a operação do primeiro elemento correto como "manter"
					$alteracoes_turmas[] = array(
						'operacao' => 'manter',
						'elemento' => $turma_disciplina,
						'link_moodle' => $turma_disciplina->obter_link_moodle(),
						'caminho_curso_moodle' => $caminho_curso_moodle
					);
				}
				else
				{
					// Obter caminho do curso Moodle atualmente associado à turma
					$curso_moodle_atual = $CI->db->query(
						$CI->consultas_sql->mdl_curso_com_caminho_mdl_categoria(false, true),
						array($turma_disciplina->id, (isset($turma_disciplina->id_mdl_course) ? $turma_disciplina->id_mdl_course : 0))
					)->row();
					$caminho_curso_moodle_atual = (isset($curso_moodle_atual)) ? formatar_caminho($curso_moodle_atual->curso_com_caminho) : null;

					if (isset($id_mdl_course))
					{
						$descricao = 'Ajustar curso do Moodle para o seguinte: ' . anchor_popup($elemento->obter_link_moodle(), $caminho_curso_moodle);
					}
					else
					{
						$descricao = 'Remover curso do Moodle associado incorretamente';
					}

					// Se não houver elemento com os dados corretos, ajustar o primeiro elemento
					$alteracoes_turmas[] = array(
						'operacao' => 'atualizar',
						'elemento' => $turma_disciplina,
						'link_moodle' => $turma_disciplina->obter_link_moodle(),
						'caminho_curso_moodle' => $caminho_curso_moodle_atual,
						'descricao' => $descricao,
						'array_para_base' => array(
							'id' => $turma_disciplina->id,
							'id_mdl_course' => $id_mdl_course
						)
					);
				}
			}
			else
			{
				// Se não houver uma turma para a disciplina, incluir
				$alteracoes_turmas[] = array(
					'operacao' => 'cadastrar',
					'elemento' => $elemento,
					'link_moodle' => $elemento->obter_link_moodle(),
					'caminho_curso_moodle' => $caminho_curso_moodle,
					'array_para_base' => array(
						'id_classe' => $classe->id,
						'id_disciplina' => $disciplina->id,
						'id_mdl_course' => $id_mdl_course
					)
				);
			}
		}

		foreach ($classe->turmas as $turma)
		{
			$alteracao_turma = obj_array_search_id(
				obj_array_map_prop(
					$alteracoes_turmas,
					'elemento'
				),
				$turma->id
			);

			if (!isset($alteracao_turma))
			{
				$caminho_curso_moodle_atual = formatar_caminho(
					obj_prop_val(
						$CI->db->query(
							$CI->consultas_sql->mdl_curso_com_caminho_mdl_categoria(false, true),
							array($turma->id, (isset($turma->id_mdl_course) ? $turma->id_mdl_course : 0))
						)->row(),
						'curso_com_caminho'
					)
				);

				// Se não houver definição de alteração para alguma turma da estrutura atual, excluir
				$alteracoes_turmas[] = array(
					'operacao' => 'remover',
					'elemento' => $turma,
					'link_moodle' => $turma->obter_link_moodle(),
					'caminho_curso_moodle' => $caminho_curso_moodle_atual
				);
			}
		}

		return $alteracoes_turmas;
	}

	/**
	 * Obter alterações de avaliações
	 *
	 * Retorna alterações na estrutura de avaliações da turma
	 * @return array
	 */
	protected function _obter_alteracoes_avaliacoes($turma)
	{
		$CI = $this->CI;
		$alteracoes_avaliacoes = array();

		$projeto_bloco = $turma->disciplina->denominacao_bloco == 'Projeto de bloco';
		$ultimo_tp = ($projeto_bloco) ? 9 : 3;

		// Lista de avaliações que fazem parte do padrão das disciplinas
		$avaliacoes = array();

		carregar_classe('models/Avaliacao_model');
		for ($i=1; $i <= $ultimo_tp; $i += 2) {
			$avaliacoes[] = new Avaliacao_model(array(
				'turma' => $turma,
				'nome' => SIGLA_TESTE_PERFORMANCE . $i,
				'avaliacao_final' => false
			));
		}
		$avaliacoes[] = new Avaliacao_model(array(
			'turma' => $turma,
			'nome' => (($projeto_bloco === false) ? NOME_ASSESSMENT_FINAL : NOME_APRESENTACAO_PROJETO_FINAL),
			'avaliacao_final' => true
		));

		foreach ($avaliacoes as $avaliacao)
		{
			$modulo_moodle = null;
			$caminho_modulo_moodle = null;
			$descricao = '';
			$array_para_base = array(
				'nome' => $avaliacao->nome,
				'avaliacao_final' => ($avaliacao->avaliacao_final) ? 1 : 0
			);
			$modulos_adicionar = array();
			$modulos_remover = array();

			if (isset($turma->id_mdl_course))
			{
				// Obter a instância do módulo que mais se aproxima do nome da avaliação, dentro do curso Moodle da turma
				$modulo_moodle = $CI->db->query(
					$CI->consultas_sql->mdl_modulo_com_caminho_mdl_categoria($avaliacao->nome),
					array($turma->id_mdl_course)
				)->row();

				if (isset($modulo_moodle))
				{
					$avaliacao->instances_mdl_course_modules[] = $modulo_moodle->instance;
					$caminho_modulo_moodle = formatar_caminho($modulo_moodle->modulo_com_caminho);
				}
			}

			$avaliacao_correspondente = null;

			// Obter a primeira avaliação cadastrada com o nome da avaliação
			foreach ($turma->avaliacoes as $avaliacao_turma) {
				if ($avaliacao_turma->nome === $avaliacao->nome)
				{
					$avaliacao_correspondente = $avaliacao_turma;
					break;
				}
			}

			// Se houver uma avaliação correspondente, verificar se os dados estão corretos
			if (isset($avaliacao_correspondente))
			{
				$array_para_base['id'] = $avaliacao_correspondente->id;

				// Se estiver com a marcação de "avaliação final" incorreta, ajustar
				if ($avaliacao->avaliacao_final !== $avaliacao_correspondente->avaliacao_final)
				{
					$descricao .= '<p>' . (($avaliacao->avaliacao_final) ? 'Marcar' : 'Desmarcar') . ' como "avaliação final"</p>';
				}

				if ($avaliacao->instances_mdl_course_modules != $avaliacao_correspondente->instances_mdl_course_modules)
				{
					$modulos_adicionar = array_diff($avaliacao->instances_mdl_course_modules, $avaliacao_correspondente->instances_mdl_course_modules);

					// Incluir cada módulo que falta
					foreach ($modulos_adicionar as $index => $instance_mdl_course_modules)
					{
						if ($index === 0)
						{
							$descricao .= 'Associar o(s) seguinte(s) módulos do Moodle:<ul>';
						}

						$descricao .= '<li>' . anchor_popup($avaliacao->obter_links_moodle_sem_icone($instance_mdl_course_modules)[0], $caminho_modulo_moodle) . '</li>';

						if ($index === (count($modulos_adicionar) - 1))
						{
							$descricao .= '</ul>';
						}
					}

					$modulos_remover = array_diff($avaliacao_correspondente->instances_mdl_course_modules, $avaliacao->instances_mdl_course_modules);

					$caminhos_modulos_moodle = $avaliacao_correspondente->obter_caminhos_modulos_moodle();

					// Remover cada módulo associado incorretamente
					foreach ($modulos_remover as $index => $instance_mdl_course_modules)
					{
						$index_modulo = array_search($instance_mdl_course_modules, $avaliacao_correspondente->instances_mdl_course_modules);

						if ($index === 0)
						{
							$descricao .= 'Desassociar o(s) seguinte(s) módulos do Moodle:<ul>';
						}

						$descricao .= '<li>' . anchor_popup($avaliacao_correspondente->obter_links_moodle_sem_icone($instance_mdl_course_modules)[0], formatar_caminho($caminhos_modulos_moodle[$index_modulo]->modulo_com_caminho)) . '</li>';

						if ($index === (count($modulos_remover) - 1))
						{
							$descricao .= '</ul>';
						}
					}
				}

				// Se não houver descrição de alterações a ser realizadas, manter a avaliação
				if (empty($descricao))
				{
					$alteracoes_avaliacoes[] = array(
						'operacao' => 'manter',
						'elemento' => $avaliacao_correspondente
					);
				}
				else
				{
					$alteracoes_avaliacoes[] = array(
						'operacao' => 'atualizar',
						'elemento' => $avaliacao_correspondente,
						'descricao' => $descricao,
						'array_para_base' => $array_para_base,
						'relacionamentos_n_n' => array(
							'avaliacoes_mdl_course_modules' => array(
								'cadastrar' => $modulos_adicionar,
								'remover' => $modulos_remover
							)
						)
					);
				}
			}
			else
			{
				// Se não houver uma avaliação com o mesmo nome, incluir
				$alteracoes_avaliacoes[] = array(
					'operacao' => 'cadastrar',
					'elemento' => $avaliacao,
					'array_para_base' => $array_para_base,
					'relacionamentos_n_n' => array(
						'avaliacoes_mdl_course_modules' => array(
							'cadastrar' => $avaliacao->instances_mdl_course_modules
						)
					)
				);
			}
		}

		foreach ($turma->avaliacoes as $avaliacao)
		{
			$alteracao_avaliacao = obj_array_search_id(
				obj_array_map_prop(
					$alteracoes_avaliacoes,
					'elemento'
				),
				$avaliacao->id
			);

			if (!isset($alteracao_avaliacao))
			{
				// Se não houver definição de alteração para alguma turma da estrutura atual, excluir
				$alteracoes_avaliacoes[] = array(
					'operacao' => 'remover',
					'elemento' => $avaliacao
				);
			}
		}

		return $alteracoes_avaliacoes;
	}

	/**
	 * Obter alterações de competências
	 *
	 * Retorna alterações na estrutura de competências da turma
	 * @return array
	 */
	protected function _obter_alteracoes_competencias($turma)
	{
		$CI = $this->CI;
		$alteracoes_competencias = array();

		$rubricas = $this->_obter_rubricas_alteracoes($turma);

		carregar_classe('models/Competencia_model');
		$competencias = array();
		foreach ($rubricas as $rubrica)
		{
			$tag_competencia = array();

			preg_match('/\[c\]\s*([0-9]*).*\[\/c\].*/', $rubrica->descricao, $tag_competencia);

			if (!empty($tag_competencia))
			{
				if (empty(obj_array_search_prop($competencias, 'codigo', $tag_competencia[1])))
				{
					$competencias[] = new Competencia_model(array(
						'codigo' => $tag_competencia[1],
						'turma' => $turma
					));
				}
			}
		}

		foreach ($competencias as $competencia)
		{
			$competencia_correspondente = null;

			// Obter a primeira competência cadastrada com o código
			foreach ($turma->competencias as $competencia_turma) {
				if ($competencia_turma->codigo == $competencia->codigo)
				{
					$competencia_correspondente = $competencia_turma;

					// Se houver uma competência correspondente, manter
					$alteracoes_competencias[] = array(
						'operacao' => 'manter',
						'elemento' => $competencia_turma
					);
					break;
				}
			}

			if (!isset($competencia_correspondente))
			{
				// Se não houver uma competência com o mesmo código, incluir
				$alteracoes_competencias[] = array(
					'operacao' => 'cadastrar',
					'elemento' => $competencia,
					'array_para_base' => array(
						'codigo' => $competencia->codigo,
						'id_turma' => $turma->id
					)
				);
			}
		}

		foreach ($turma->competencias as $competencia)
		{
			$alteracao_competencia = obj_array_search_prop(
				obj_array_map_prop(
					$alteracoes_competencias,
					'elemento'
				),
				'codigo',
				$competencia->codigo
			);

			if (empty($alteracao_competencia))
			{
				// Se não houver definição de alteração para alguma turma da estrutura atual, excluir
				$alteracoes_competencias[] = array(
					'operacao' => 'remover',
					'elemento' => $competencia
				);
			}
		}

		return $alteracoes_competencias;
	}

	/**
	 * Obter alterações de subcompetências
	 *
	 * Retorna alterações na estrutura de subcompetências da competência
	 * @return array
	 */
	protected function _obter_alteracoes_subcompetencias($turma)
	{
		$CI = $this->CI;
		carregar_classe('models/Competencia_model');

		$alteracoes_subcompetencias = array();
		$subcompetencias = array();

		$rubricas = $this->_obter_rubricas_alteracoes($turma);
		$subcompetencias_turma = $turma->obter_subcompetencias();

		foreach ($rubricas as $rubrica)
		{
			$tag_subcompetencia = array();

			preg_match('/\[c\]\s*([0-9]*)\s*\.\s*([0-9]*)[\s\.]*(.*)\s*\[\/c\].*/', $rubrica->descricao, $tag_subcompetencia);

			if (!empty($tag_subcompetencia))
			{
				$competencia = obj_array_search_prop($turma->competencias, 'codigo', $tag_subcompetencia[1])[0];
				$obrigatoria = preg_match('/\[\s*subcomp.*obrigat.*\].*/', $rubrica->descricao) !== false;
				$codigo_completo = $tag_subcompetencia[1] . '.' . $tag_subcompetencia[2] . (($obrigatoria) ? SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE : '');

				if (empty(obj_array_search_prop($subcompetencias, 'codigo_completo', $codigo_completo)))
				{
					$subcompetencias[] = new Subcompetencia_model(array(
						'competencia' => $competencia,
						'codigo_completo' => $codigo_completo,
						'nome' => trim($tag_subcompetencia[3]),
						'obrigatoria' => $obrigatoria,
						'rubrica' => $rubrica
					));
				}
			}
		}

		foreach ($subcompetencias as $subcompetencia)
		{
			$array_para_base = array(
				'nome' => $subcompetencia->nome,
				'codigo' => $subcompetencia->obter_codigo_subcompetencia(),
				'id_competencia' => $subcompetencia->competencia->id,
				'obrigatoria' => $subcompetencia->obrigatoria
			);

			$subcompetencia_correspondente = null;

			// Obter a primeira subcompetência cadastrada com o código
			foreach ($subcompetencias_turma as $subcompetencia_turma) {
				if ($subcompetencia_turma->obter_codigo_sem_obrigatoriedade() === $subcompetencia->obter_codigo_sem_obrigatoriedade())
				{
					$subcompetencia_correspondente = $subcompetencia_turma;
					break;
				}
			}

			// Se houver uma subcompetência correspondente, verificar se os dados estão corretos
			if (isset($subcompetencia_correspondente))
			{
				if (false && $subcompetencia_correspondente->id == 1783)
				{
					var_dump($subcompetencia);
					var_dump($subcompetencia_correspondente);
					die();
				}

				$array_para_base['id'] = $subcompetencia_correspondente->id;
				$descricao = '';

				// Se estiver com um nome diferente da rubrica, ajustar
				if ($subcompetencia->nome !== $subcompetencia_correspondente->nome)
				{
					$descricao .= '<p>Alterar nome para "' . $subcompetencia->nome . '"</p>';
				}

				// Se estiver com a obrigatoriedade incorreta, ajustar
				if ($subcompetencia->obrigatoria !== $subcompetencia_correspondente->obrigatoria)
				{
					$descricao .= '<p>Definir como ' . ((!$subcompetencia->obrigatoria) ? 'não ' : '') . ' obrigatória</p>';;
				}

				// Se não houver descrição de alterações a ser realizadas, manter a avaliação
				if (empty($descricao))
				{
					$alteracoes_subcompetencias[] = array(
						'operacao' => 'manter',
						'elemento' => $subcompetencia_correspondente
					);
				}
				else
				{
					$alteracoes_subcompetencias[] = array(
						'operacao' => 'atualizar',
						'elemento' => $subcompetencia_correspondente,
						'descricao' => $descricao,
						'array_para_base' => $array_para_base
					);
				}
			}
			else
			{
				// Se não houver uma subcompetência com o mesmo código, incluir
				$alteracoes_subcompetencias[] = array(
					'operacao' => 'cadastrar',
					'elemento' => $subcompetencia,
					'array_para_base' => $array_para_base
				);
			}
		}


		foreach ($subcompetencias_turma as $subcompetencia)
		{
			$alteracao_subcompetencia = obj_array_search_prop(
				obj_array_map_prop(
					$alteracoes_subcompetencias,
					'elemento'
				),
				'codigo_completo',
				$subcompetencia->codigo_completo
			);

			if (empty($alteracao_subcompetencia))
			{
				// Se não houver definição de alteração para alguma subcompetência da estrutura atual, excluir
				$alteracoes_subcompetencias[] = array(
					'operacao' => 'remover',
					'elemento' => $subcompetencia
				);
			}
		}

		return $alteracoes_subcompetencias;
	}

	/**
	 * Obter rubricas de alterações
	 *
	 * Retorna as rubricas das avaliações da turma a partir da lista de alterações
	 */
	protected function _obter_rubricas_alteracoes($turma)
	{
		carregar_classe('models/Rubrica_model');

		$avaliacoes_instances = array();
		$avaliacao_instances = array();

		if (isset($this->alteracoes_estrutura['avaliacoes']))
		{
			foreach ($this->alteracoes_estrutura['avaliacoes'] as $alteracao)
			{
				$avaliacao = $alteracao['elemento'];
				$operacao = $alteracao['operacao'];

				$avaliacao_instances = array(
					'avaliacao' => $avaliacao
				);

				if (in_array($operacao, array('manter', 'remover')))
				{
					$avaliacao_instances['instances'] = $avaliacao->instances_mdl_course_modules;
				}
				else if ($operacao === 'cadastrar')
				{
					$avaliacao_instances['instances'] = $alteracao['relacionamentos_n_n']['avaliacoes_mdl_course_modules']['cadastrar'];
				}
				else if ($operacao === 'atualizar')
				{
					$avaliacao_instances['instances'] = array_merge(
						$alteracao['relacionamentos_n_n']['avaliacoes_mdl_course_modules']['cadastrar'],
						$alteracao['relacionamentos_n_n']['avaliacoes_mdl_course_modules']['remover']
					);
				}

				$avaliacoes_instances[] = $avaliacao_instances;
			}
		}
		else
		{
			foreach ($turma->avaliacoes as $avaliacao)
			{
				$avaliacoes_instances[] = array(
					'avaliacao' => $avaliacao,
					'instances' => $avaliacao->instances_mdl_course_modules
				);
			}
		}

		$rubricas = array();
		foreach ($avaliacoes_instances as $avaliacao_instances)
		{
			if (!empty($avaliacao_instances['instances']))
			{
				$rubricas = $this->CI->db->query(
					$this->CI->consultas_sql->mdl_rubricas_instance(count($avaliacao_instances['instances'])),
					$avaliacao_instances['instances']
				)->custom_result_object('Rubrica_model');
			}

			foreach ($rubricas as $rubrica)
			{
				$rubrica->avaliacao = $avaliacao_instances['avaliacao'];
			}
		}

		return $rubricas;
	}

	/**
	 * Organizar alterações
	 *
	 * Ajusta as alterações de estrutura
	 */
	protected function _organizar_alteracoes($value='')
	{
		$alteracoes_estrutura = $this->alteracoes_estrutura;

		foreach ($alteracoes_estrutura as $tipo_item => $alteracoes)
		{
			// Usar referências a $alteracoes não altera o array $alteracoes_estrutura
			// por isso está sendo usado $alteracoes_estrutura[$tipo_item]
			foreach ($alteracoes_estrutura[$tipo_item] as $index => $alteracao)
			{
				// Inclui um vetor de atributos para serem aplicados ao checkbox
				$alteracoes_estrutura[$tipo_item][$index]['atributos'] = array();

				// Define a descrição da operação com o nome padrão quando não há uma descrição mais específica
				if (!isset($alteracoes_estrutura[$tipo_item][$index]['descricao']))
				{
					$alteracoes_estrutura[$tipo_item][$index]['descricao'] = $this->operacoes_descricoes[$alteracoes_estrutura[$tipo_item][$index]['operacao']];
				}
			}

			if (in_array($tipo_item, array('turmas', 'competencias', 'subcompetencias')))
			{
				usort($alteracoes_estrutura[$tipo_item], array($this, '_comparar_alteracoes'));
			}

			if (in_array($tipo_item, array('avaliacoes', 'competencias')))
			{
				$turmas = obj_array_map_prop(
					$alteracoes_estrutura['turmas'],
					'elemento'
				);
				$turmas_id = obj_array_map_id($turmas, true);
				$turmas_id_disciplina = obj_array_map_id(
					obj_array_map_prop(
						$turmas,
						'disciplina'
					)
				);

				foreach ($alteracoes_estrutura[$tipo_item] as $index => $alteracao)
				{
					$turma = $alteracao['elemento']->turma;
					$item_dependencia = null;

					if (isset($turma->id))
					{
						$item_dependencia = array_search($turma->id, $turmas_id);
					}
					else
					{
						$item_dependencia = array_search($turma->disciplina->id, $turmas_id_disciplina);
					}

					if ($alteracao['operacao'] !== 'atualizar' && $alteracao['operacao'] === $alteracoes_estrutura['turmas'][$item_dependencia]['operacao'])
					{
						// Se tanto a alteração do item quanto da turma forem adicionar ou excluir, incluir dependência
						$alteracoes_estrutura[$tipo_item][$index]['atributos']['dependencia'] = 'turmas-' . $item_dependencia;
					}
				}
			}
			else if ($tipo_item === 'subcompetencias')
			{
				$competencias = obj_array_map_prop(
					$alteracoes_estrutura['competencias'],
					'elemento'
				);
				$competencias_id = obj_array_map_id($competencias, true);
				$competencias_id_disciplina_codigo = array();

				foreach ($competencias as $competencia)
				{
					$competencias_id_disciplina_codigo[] = array(
						'id_disciplina' => $competencia->turma->disciplina->id,
						'codigo' => $competencia->codigo
					);
				}

				foreach ($alteracoes_estrutura[$tipo_item] as $index => $alteracao)
				{
					$competencia = $alteracao['elemento']->competencia;
					$item_dependencia = null;

					if (isset($competencia->id))
					{
						$item_dependencia = array_search($competencia->id, $competencias_id);
					}
					else
					{
						foreach ($competencias_id_disciplina_codigo as $index => $competencia_id_disciplina_codigo)
						{
							if (
								$competencias_id_disciplina_codigo['id_disciplina'] == $competencia->turma->disciplina->id
								&& $competencias_id_disciplina_codigo['codigo'] == $competencia->codigo
							)
							{
								$item_dependencia = $index;
								break;
							}
						}
					}

					if ($alteracao['operacao'] !== 'atualizar' && $alteracao['operacao'] === $alteracoes_estrutura['competencias'][$item_dependencia]['operacao'])
					{
						// Se tanto a alteração do item quanto da turma forem adicionar ou excluir, incluir dependência
						$alteracoes_estrutura[$tipo_item][$index]['atributos']['dependencia'] = 'competencias-' . $item_dependencia;
					}
				}
			}
		}

		$this->alteracoes_estrutura = $alteracoes_estrutura;
	}

	/**
	 * Comparar alterações
	 *
	 * Compara alterações para ordenação
	 */
	protected function _comparar_alteracoes($alteracao_1, $alteracao_2)
	{
		$elemento_1 = $alteracao_1['elemento'];
		$elemento_2 = $alteracao_2['elemento'];

		$classe_elemento = get_class($elemento_1);

		if ($classe_elemento === 'Turma_model')
		{
			$disciplina_1 = $elemento_1->disciplina;
			$disciplina_2 = $elemento_2->disciplina;
		}
		else if ($classe_elemento === 'Competencia_model')
		{
			$disciplina_1 = $elemento_1->turma->disciplina;
			$disciplina_2 = $elemento_2->turma->disciplina;
		}
		else if ($classe_elemento === 'Subcompetencia_model')
		{
			$disciplina_1 = $elemento_1->competencia->turma->disciplina;
			$disciplina_2 = $elemento_2->competencia->turma->disciplina;
		}

		if ($disciplina_1->bloco->id !== $disciplina_2->bloco->id)
		{
			return strcmp($disciplina_1->bloco->nome, $disciplina_2->bloco->nome);
		}
		else if ($disciplina_1->denominacao_bloco !== $disciplina_2->denominacao_bloco)
		{
			return strcmp($disciplina_1->denominacao_bloco, $disciplina_2->denominacao_bloco);
		}
		else if ($classe_elemento === 'Competencia_model')
		{
			return $elemento_1->codigo - $elemento_2->codigo;
		}
		else if ($classe_elemento === 'Subcompetencia_model')
		{
			if ($elemento_1->competencia->codigo !== $elemento_2->competencia->codigo)
			{
				return $elemento_1->competencia->codigo - $elemento_2->competencia->codigo;
			}
			else
			{
				return $elemento_1->obter_codigo_subcompetencia() - $elemento_2->obter_codigo_subcompetencia();
			}
		}
	}

}
