<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Geracao_estrutura {
	private $CI;

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

		$this->_preparar_alteracoes($classe);

		foreach ($this->alteracoes_estrutura['turmas'] as $alteracao)
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
				'class',
				'obj_array',
				'format'
			))
			->library('Consultas_SQL')
		;

		$classe_registro = get_class($registro);
		$registro->popular(true);
		$alteracoes_estrutura = $this->alteracoes_estrutura;

		// Se o registro da estrutura for uma classe, a estrutura é de turmas
		if ($classe_registro === 'Classe_model')
		{
			if (!isset($alteracoes_estrutura['turmas']))
			{
				$alteracoes_estrutura['turmas'] = array();
			}

			carregar_classe('models/Disciplina_model');
			// Listar todas as disciplinas que fazem parte dos blocos do programa da classe
			$disciplinas = $CI->db
				->select('d.*')
				->join('programas_blocos pb', 'pb.id_bloco = d.id_bloco')
				->join('classes c', 'c.id_programa = pb.id_programa')
				->where('c.id', $registro->id)
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

				$curso_moodle = $CI->db->query(
					$CI->consultas_sql->mdl_curso_com_caminho_mdl_categoria(true),
					array($registro->id, $disciplina->nome)
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
					'classe' => $registro,
					'id_mdl_course' => $id_mdl_course
				));
				$elemento->popular(true);

				$turma_disciplina = null;

				// Obter a primeira turma cadastrada para a disciplina na classe
				foreach ($registro->turmas as $turma) {
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
						$alteracoes_estrutura['turmas'][] = array(
							'operacao' => 'manter',
							'elemento' => $turma_disciplina,
							'link_moodle' => $turma_disciplina->obter_link_moodle(),
							'caminho_curso_moodle' => $caminho_curso_moodle
						);
					}
					else
					{
						// Obter caminho do curso Moodle atualmente associado à turma
						$caminho_curso_moodle_atual = formatar_caminho($CI->db->query(
								$CI->consultas_sql->mdl_curso_com_caminho_mdl_categoria(false, true),
								array($turma_disciplina->id, (isset($turma_disciplina->id_mdl_course) ? $turma_disciplina->id_mdl_course : 0))
							)->row()->curso_com_caminho);

						if (isset($id_mdl_course))
						{
							$descricao = 'Ajustar curso do Moodle para o seguinte: ' . anchor_popup($elemento->obter_link_moodle(), $caminho_curso_moodle);
						}
						else
						{
							$descricao = 'Remover curso do Moodle associado incorretamente';
						}

						// Se não houver elemento com os dados corretos, ajustar o primeiro elemento
						$alteracoes_estrutura['turmas'][] = array(
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
					$alteracoes_estrutura['turmas'][] = array(
						'operacao' => 'cadastrar',
						'elemento' => $elemento,
						'link_moodle' => $elemento->obter_link_moodle(),
						'caminho_curso_moodle' => $caminho_curso_moodle,
						'array_para_base' => array(
							'id_classe' => $registro->id,
							'id_disciplina' => $disciplina->id,
							'id_mdl_course' => $id_mdl_course
						)
					);
				}
			}

			foreach ($registro->turmas as $turma)
			{
				$alteracao_turma = obj_array_search_id(
					obj_array_map_prop(
						$alteracoes_estrutura['turmas'],
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
					$alteracoes_estrutura['turmas'][] = array(
						'operacao' => 'remover',
						'elemento' => $turma,
						'link_moodle' => $turma->obter_link_moodle(),
						'caminho_curso_moodle' => $caminho_curso_moodle_atual
					);
				}
			}
		}
		// Se o registro da estrutura for uma turma, a estrutura é de avaliações e competências
		else if ($classe_registro === 'Turma_model')
		{
			if (!isset($alteracoes_estrutura['avaliacoes']))
			{
				$alteracoes_estrutura['avaliacoes'] = array();
			}
			if (!isset($alteracoes_estrutura['competencias']))
			{
				$alteracoes_estrutura['competencias'] = array();
			}

			$projeto_bloco = $registro->disciplina->denominacao_bloco == 'Projeto de bloco';
			$ultimo_tp = ($projeto_bloco) ? 9 : 3;

			// Lista de avaliações que fazem parte do padrão das disciplinas
			$avaliacoes = array();

			carregar_classe('models/Avaliacao_model');
			for ($i=1; $i <= $ultimo_tp; $i += 2) {
				$avaliacoes[] = new Avaliacao_model(array(
					'turma' => $registro,
					'nome' => SIGLA_TESTE_PERFORMANCE . $i,
					'avaliacao_final' => false
				));
			}
			$avaliacoes[] = new Avaliacao_model(array(
				'turma' => $registro,
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

				if (isset($registro->id_mdl_course))
				{
					// Obter a instância do módulo que mais se aproxima do nome da avaliação, dentro do curso Moodle da turma
					$modulo_moodle = $CI->db->query(
						$CI->consultas_sql->mdl_modulo_com_caminho_mdl_categoria($avaliacao->nome),
						array($registro->id_mdl_course)
					)->row();

					if (isset($modulo_moodle))
					{
						$avaliacao->instances_mdl_course_modules[] = $modulo_moodle->instance;
						$caminho_modulo_moodle = formatar_caminho($modulo_moodle->modulo_com_caminho);
					}
				}

				$avaliacao_correspondente = null;

				// Obter a primeira avaliação cadastrada com o nome da avaliação
				foreach ($registro->avaliacoes as $avaliacao_registro) {
					if ($avaliacao_registro->nome === $avaliacao->nome)
					{
						$avaliacao_correspondente = $avaliacao_registro;
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
						$alteracoes_estrutura['avaliacoes'][] = array(
							'operacao' => 'manter',
							'elemento' => $avaliacao_correspondente
						);
					}
					else
					{
						$alteracoes_estrutura['avaliacoes'][] = array(
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
					$alteracoes_estrutura['avaliacoes'][] = array(
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

			foreach ($registro->avaliacoes as $avaliacao)
			{
				$alteracao_avaliacao = obj_array_search_id(
					obj_array_map_prop(
						$alteracoes_estrutura['avaliacoes'],
						'elemento'
					),
					$avaliacao->id
				);

				if (!isset($alteracao_avaliacao))
				{
					// Se não houver definição de alteração para alguma turma da estrutura atual, excluir
					$alteracoes_estrutura['avaliacoes'][] = array(
						'operacao' => 'remover',
						'elemento' => $avaliacao
					);
				}
			}

		}

		$this->alteracoes_estrutura = $alteracoes_estrutura;
	}

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

			if ($tipo_item === 'turmas')
			{
				usort($alteracoes_estrutura[$tipo_item], array($this, '_comparar_alteracoes_turmas'));
			}
			else if ($tipo_item === 'avaliacoes')
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
						// Se tanto a alteração da avaliação quanto da turma forem adicionar ou excluir, incluir dependência
						$alteracoes_estrutura[$tipo_item][$index]['atributos']['dependencia'] = 'turmas-' . $item_dependencia;
					}

				}
			}
		}

		$this->alteracoes_estrutura = $alteracoes_estrutura;
	}

	protected function _comparar_alteracoes_turmas($alteracao_1, $alteracao_2)
	{
		$disciplina_1 = $alteracao_1['elemento']->disciplina;
		$disciplina_2 = $alteracao_2['elemento']->disciplina;

		$item_comparacao_1;
		$item_comparacao_2;

		if ($disciplina_1->bloco->id !== $disciplina_2->bloco->id)
		{
			$item_comparacao_1 = $disciplina_1->bloco->id;
			$item_comparacao_2 = $disciplina_2->bloco->id;
		}
		else
		{
			$item_comparacao_1 = $disciplina_1->denominacao_bloco;
			$item_comparacao_2 = $disciplina_2->denominacao_bloco;
		}

		return strcmp($item_comparacao_1, $item_comparacao_2);
	}

}
