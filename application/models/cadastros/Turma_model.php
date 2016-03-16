<?php
class Turma_model extends Grocery_CRUD_Model  {

	/**
	 * Obter link Moodle
	 *
	 * Retorna HTML de um link para a categoria da turma no LMS
	 * @return string
	 */
	public function obter_link_moodle($valor, $linha) {
		$this->load->helper('url');

		return anchor_popup(
			URL_BASE_LMS . '/course/index.php?categoryid=' . $linha->id_mdl_course_category,
			'<img title="Acessar turma no Moodle" class="tamanho-icone float-right" src="' . base_url('assets/img/moodle-m-65x46.png') . '">'
		);
	}

	/**
	 * Obter nome com link Moodle
	 *
	 * Retorna o nome da turma com um link para a categoria da turma no LMS, se houver
	 * @return string
	 */
	public function obter_nome_com_link_moodle($valor, $linha) {
		$retorno = $linha->nome;

		if ($linha->id_mdl_course_category) {
			$retorno .= $this->obter_link_moodle($valor, $linha);
		}

		return $retorno;
	}

	/**
	 * Obter categorias Moodle
	 *
	 * Retorna o caminho completo de todas as categorias do Moodle, ordenadas por hierarquia
	 * @return array
	 */
	public function obter_categorias_moodle()

	{
		$consulta = $this->db->query("
			select c.id,
				CONCAT_WS(' > ', c6.name, c5.name, c4.name, c3.name, c2.name, c.name) categoria_com_caminho
			from lmsinfne_mdl.mdl_course_categories c
			  left join lmsinfne_mdl.mdl_course_categories c2 on c2.id = c.parent
			  left join lmsinfne_mdl.mdl_course_categories c3 on c3.id = c2.parent
			  left join lmsinfne_mdl.mdl_course_categories c4 on c4.id = c3.parent
			  left join lmsinfne_mdl.mdl_course_categories c5 on c5.id = c4.parent
			  left join lmsinfne_mdl.mdl_course_categories c6 on c6.id = c5.parent
			order by c.path;
		");

		$categorias_moodle = array();

		foreach ($consulta->result() as $linha) {
			$categorias_moodle[$linha->id] = $linha->categoria_com_caminho;
		}

		return $categorias_moodle;
	}

}