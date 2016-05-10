<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Geracao_estrutura {
	private $CI;
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

		$classe = $CI->db->where('id', $id_classe)->get('classes')->custom_row_object(0, 'Classe_model');

		return $this->_obter_estrutura_padrao($classe);
	}

	/**
	 * Obter estrutura padrão
	 *
	 * Retorna as alterações necessárias para gerar a estrutura de turmas, disciplinas,
	 * avaliações, competências e/ou rubricas, a partir do registro informado
	 * @return string
	 */
	public function _obter_estrutura_padrao($registro)
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
		$estrutura_atual = $registro->popular(true);
		$alteracoes_estrutura = array();

		foreach (get_object_vars($estrutura_atual) as $propriedade => $valor) {
			// Se o registro da estrutura for uma classe, a estrutura é de turmas e seus subcadastros
			if ($classe_registro === 'Classe_model')
			{
				if ($propriedade === 'turmas')
				{
					$alteracoes_estrutura['turmas'] = array();

					carregar_classe('models/Disciplina_model');
					// Listar todas as disciplinas que fazem parte dos blocos do programa da classe
					$disciplinas = $CI->db
						->select('d.*')
						->join('programas_blocos pb', 'pb.id_bloco = d.id_bloco')
						->join('classes c', 'c.id_programa = pb.id_programa')
						->where('c.id', $estrutura_atual->id)
						->get('disciplinas d')
						->custom_result_object('Disciplina_model')
					;

					foreach ($disciplinas as $disciplina)
					{
						// Preenche os dados da instância da disciplina
						$disciplina->popular(true);

						$id_mdl_course = null;
						$caminho_curso_moodle = null;

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
						foreach ($estrutura_atual->turmas as $turma) {
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

								// Se não houver elemento com os dados corretos, ajustar o primeiro elemento
								$alteracoes_estrutura['turmas'][] = array(
									'operacao' => 'atualizar',
									'elemento' => $turma_disciplina,
									'link_moodle' => $turma_disciplina->obter_link_moodle(),
									'caminho_curso_moodle' => $caminho_curso_moodle_atual,
									'descricao' => 'Ajustar curso do Moodle para o seguinte: ' . anchor($elemento->obter_link_moodle(), $caminho_curso_moodle)
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
								'caminho_curso_moodle' => $caminho_curso_moodle
							);
						}
					}

					foreach ($estrutura_atual->turmas as $turma)
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
							$caminho_curso_moodle_atual = obj_prop_val($CI->db->query(
								$CI->consultas_sql->mdl_curso_com_caminho_mdl_categoria(false, true),
								array($turma->id, (isset($turma->id_mdl_course) ? $turma->id_mdl_course : 0))
							)->row(), 'curso_com_caminho');

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
			}
		}

		foreach ($alteracoes_estrutura as $tipo_item => $alteracoes)
		{
			// Usar referências a $alteracoes não altera o array $alteracoes_estrutura
			// por isso está sendo usado $alteracoes_estrutura[$tipo_item]
			foreach ($alteracoes_estrutura[$tipo_item] as $index => $alteracao)
			{
				// Define a descrição da operação com o nome padrão quando não há uma descrição mais específica
				if (!isset($alteracoes_estrutura[$tipo_item][$index]['descricao']))
				{
					$alteracoes_estrutura[$tipo_item][$index]['descricao'] = $this->operacoes_descricoes[$alteracoes_estrutura[$tipo_item][$index]['operacao']];
				}
			}

			if ($tipo_item === 'turmas')
			{
				usort($alteracoes_estrutura[$tipo_item], array($this, 'comparar_alteracoes_turmas'));
			}
		}

		return $alteracoes_estrutura;
	}

	protected function comparar_alteracoes_turmas($alteracao_1, $alteracao_2)
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
