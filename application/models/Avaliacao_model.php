<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Avaliacao_model extends CI_Model {
	public $id;
	public $nome;
	public $avaliacao_final;
	public $rubricas = array();
	public $competencias = array();

	public function __construct($dados = null)
	{
		if (is_array($dados))
		{
			$this->id = $dados['id'];
			$this->nome = $dados['nome'];
			$this->avaliacao_final = $dados['avaliacao_final'];
		}
	}

	/**
	 * Obter subcompetências
	 *
	 * Retorna todas as subcompetências das rubricas da avaliação
	 * @return array
	 */
	public function obter_subcompetencias()
	{
		$subcompetencias = array();

		foreach ($this->competencias as $competencia)
		{
			$subcompetencias = array_merge_recursive($subcompetencias, $competencia->subcompetencias);
		}

		return $subcompetencias;
	}

	/**
	 * Obter quantidade de rubricas por subcompetência
	 *
	 * Retorna todas as subcompetências da avaliação
	 * e a quantidade de rubricas associadas a cada uma
	 * @return array
	 */
	public function obter_qtd_rubricas_subcompetencias()
	{
		$qtd_rubricas_subcompetencias = array();

		foreach ($this->rubricas as $rubrica)
		{
			foreach ($rubrica->subcompetencias as $subcompetencia)
			{
				$codigo = $subcompetencia->obter_codigo_sem_obrigatoriedade();

				if (isset($qtd_rubricas_subcompetencias[$codigo]))
				{
					$qtd_rubricas_subcompetencias[$codigo]++;
				}
				else
				{
					$qtd_rubricas_subcompetencias[$codigo] = 1;
				}
			}
		}

		return $qtd_rubricas_subcompetencias;
	}

	/**
	 * Obter links de avaliações no Moodle
	 *
	 * Retorna HTML de links para as avaliações da turma no LMS com o ícone do Moodle
	 * @return string
	 */
	public function obter_links_moodle($id_avaliacao = null)
	{
		if ($id_avaliacao === null)
		{
			$id_avaliacao = $this->id;
		}

		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->mdl_modulos_avaliacoes(), array($id_avaliacao));

		$links = '';

		foreach ($consulta->result() as $lin)
		{
			$links .= anchor_popup(
				URL_BASE_LMS . '/mod/assign/view.php?id=' . $lin->id,
				img(
					array(
						'src' => base_url('assets/img/moodle-m-65x46.png'),
						'alt' => $lin->name,
						'title' => $lin->name,
						'class' => 'tamanho-icone'
					)
				)
			);
		}

		return $links;
	}

}
