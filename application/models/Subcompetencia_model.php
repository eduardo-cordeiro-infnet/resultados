<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Subcompetencia_model extends CI_Model {
	public $id;
	public $competencia;
	public $codigo_completo;
	public $nome;
	public $obrigatoria;
	public $rubrica;

	private $populando = false;

	public function __construct($param = null)
	{
		if (is_array($param))
		{
			if (isset($param['id']))
			{
				$this->id = $param['id'];
			}
			if (isset($param['competencia']))
			{
				$this->competencia = $param['competencia'];
			}
			if (isset($param['codigo_completo']))
			{
				$this->codigo_completo = $param['codigo_completo'];
			}
			if (isset($param['nome']))
			{
				$this->nome = $param['nome'];
			}
			if (isset($param['obrigatoria']))
			{
				$this->obrigatoria = $param['obrigatoria'];
			}
			if (isset($param['rubrica']))
			{
				$this->rubrica = $param['rubrica'];
			}
		}
		else if (isset($param))
		{
			$this->id = $param;
		}

		$this->load->database();
	}

	/**
	 * Popular
	 *
	 * Preenche as propriedades da instância com valores obtidos na base a partir do ID da subcompetência
	 * Retorna esta própria instância para permitir concatenação de funções ou null se não houver ID definido
	 * Se for informado $id_avaliacao, apenas subcompetências da avaliação são incluídas ao popular a competência
	 * @return Subcompetencia_model
	 */
	public function popular($apenas_estrutura = false, $id_avaliacao = null)
	{
		if (isset($this->id))
		{
			if (!$this->populando)
			{
				$this->populando = true;

				$dados_instancia = $this->db->get_where('subcompetencias', array('id' => $this->id))->row();

				if (!isset($this->codigo_completo))
				{
					$this->codigo_completo = $dados_instancia->codigo_completo_calc;
				}
				if (!isset($this->nome))
				{
					$this->nome = $dados_instancia->nome;
				}
				if (!isset($this->obrigatoria))
				{
					$this->obrigatoria = $dados_instancia->obrigatoria == 1;
				}

				if (!isset($this->competencia))
				{
					carregar_classe('models/Competencia_model');
					$this->competencia = new Competencia_model($dados_instancia->id_competencia);
				}
				$this->competencia->popular($apenas_estrutura, $id_avaliacao);

				$this->populando = false;
			}

			return $this;
		}
		else
		{
			return null;
		}
	}

	public function comparar($scmp1, $scmp2)
	{
		return strcmp($scmp1->codigo_completo, $scmp2->codigo_completo);
	}

	/**
	 * Obter código sem obrigatoriedade
	 *
	 * Retorna o código completo da subcompetência sem asterisco,
	 * mesmo que seja obrigatória
	 * @return string
	 */
	public function obter_codigo_sem_obrigatoriedade()
	{
		return str_replace(SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE, '', $this->codigo_completo);
	}

	/**
	 * Obter código da competência
	 *
	 * Retorna o código da competência da qual esta subcompetência faz parte
	 * @return string
	 */
	public function obter_codigo_competencia()
	{
		return explode('.', $this->codigo_completo)[0];
	}

	/**
	 * Obter código da subcompetência
	 *
	 * Retorna o código da subcompetência sem o código da competência
	 * @return string
	 */
	public function obter_codigo_subcompetencia()
	{
		return explode('.', $this->obter_codigo_sem_obrigatoriedade())[1];
	}

}
