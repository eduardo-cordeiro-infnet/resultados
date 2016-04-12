<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Consultas_SQL {
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->database();
	}

	/**
	 * Retorna todas as categorias do Moodle com o caminho hierárquico
	 * @return string
	 */
	public function mdl_categorias_com_caminho()
	{
		return "
			select c.id,
				CONCAT_WS(' > ', c6.name, c5.name, c4.name, c3.name, c2.name, c.name) categoria_com_caminho
			from lmsinfne_mdl.mdl_course_categories c
				left join lmsinfne_mdl.mdl_course_categories c2 on c2.id = c.parent
				left join lmsinfne_mdl.mdl_course_categories c3 on c3.id = c2.parent
				left join lmsinfne_mdl.mdl_course_categories c4 on c4.id = c3.parent
				left join lmsinfne_mdl.mdl_course_categories c5 on c5.id = c4.parent
				left join lmsinfne_mdl.mdl_course_categories c6 on c6.id = c5.parent
			order by c.path;
		";
	}

	/**
	 * Retorna todos os cursos do Moodle com o caminho hierárquico
	 * Se for informada categoria do Moodle, os cursos desta categoria são ordenados no início
	 * @return string
	 */
	public function mdl_curso_com_caminho_mdl_categoria()
	{
		return "
			select crs.id,
				CONCAT_WS(' > ', c6.name, c5.name, c4.name, c3.name, c2.name, c.name, crs.fullname) curso_com_caminho,
				case when ? in (select id from turmas where id_mdl_course_category in (c6.id, c5.id, c4.id, c3.id, c2.id, c.id)) then 1 else 0 end disciplina_turma
			from lmsinfne_mdl.mdl_course crs
				left join lmsinfne_mdl.mdl_course_categories c on c.id = crs.category
				left join lmsinfne_mdl.mdl_course_categories c2 on c2.id = c.parent
				left join lmsinfne_mdl.mdl_course_categories c3 on c3.id = c2.parent
				left join lmsinfne_mdl.mdl_course_categories c4 on c4.id = c3.parent
				left join lmsinfne_mdl.mdl_course_categories c5 on c5.id = c4.parent
				left join lmsinfne_mdl.mdl_course_categories c6 on c6.id = c5.parent
			order by disciplina_turma desc, c.path;
		";
	}

	/**
	 * Retorna todas as disciplinas, com o nome do bloco, se houver
	 * Disciplinas de blocos que estejam associados à turma especificada são ordenadas no início
	 * @param boolean apenas_sem_turma não retornar disciplinas já associadas a esta turma
	 * @return string
	 */
	public function disciplinas_blocos_turma($apenas_sem_turma)
	{
		$sql = "
			select d.id,
				CONCAT(d.nome, case when b.id is not null then CONCAT(' (', b.nome, ')') end) disciplina_bloco,
				case when b.id
					in (select id_bloco_red from disciplinas_turmas where id_turma = ?)
				then 1 else 0 end bloco_turma
			from disciplinas d
				left join blocos b on b.id = d.id_bloco
				left join disciplinas_turmas dt on dt.id_turma = ?
					and dt.id_disciplina = d.id
					and dt.id <> ?";

		if ($apenas_sem_turma)
		{
			$sql .= "
			where dt.id is null";
		}

		$sql .= "
			order by bloco_turma desc, b.id, d.nome;
		";

		return $sql;
	}

	/**
	 * Retorna disciplinas que estão associadas a turmas no seguinte formato:
	 * {Sigla da escola} > {Programa} > {Turma} > {Bloco} > {Disciplina}
	 * @return string
	 */
	public function disciplinas_turmas_com_caminho($disciplina_turma_especifica = false, $apenas_com_rubricas_subcompetencias = false, $sem_disciplina_turma_selecionada = false)
	{
		$db = $this->CI->db;

		$db->select("dt.id,
			CONCAT(
				CONCAT_WS(' > ', e.sigla, p.nome, t.nome, b.nome, d.nome),
					case
						when d.denominacao_bloco is not null then CONCAT(' (', d.denominacao_bloco, ')')
						else ''
					end
			) disciplina_turma_com_caminho,
			CONCAT(e.sigla, p.nome, t.nome, b.nome) bloco_com_caminho,
			d.denominacao_bloco"
		);

		$db
			->from('disciplinas_turmas dt')
			->join('disciplinas d', 'd.id = dt.id_disciplina')
			->join('blocos b', 'b.id = d.id_bloco')
			->join('turmas t', 't.id = dt.id_turma')
			->join('programas p', 'p.id = t.id_programa')
			->join('escolas e', 'e.id = p.id_escola')
		;


		//Se for informada uma disciplina específica, o campo de disciplina selecionada é necessário
		if (!$sem_disciplina_turma_selecionada || $disciplina_turma_especifica)
		{
			$db
				->select('case when dt.id = ? then 1 else 0 end disciplina_turma_selecionada', false)
				->order_by('disciplina_turma_selecionada', 'desc')
			;
		}

		$db
			->order_by('bloco_com_caminho')
			->order_by('denominacao_bloco')
		;

		if ($apenas_com_rubricas_subcompetencias)
		{
			$db->where('exists (
					select 1 from subcompetencias_mdl_gradingform_rubric_criteria sgrc
						join subcompetencias scmp on scmp.id = sgrc.id_subcompetencia
						join competencias cmp on cmp.id = scmp.id_competencia
					where cmp.id_disciplina_turma = dt.id
				)'
			);
		}

		if ($disciplina_turma_especifica)
		{
			$db->having('disciplina_turma_selecionada', 1);
		}

		return $db->get_compiled_select();
	}

	/**
	 * Retorna competências de uma disciplinas associada a uma turma específica
	 * @return string
	 */
	public function competencias_disciplina_turma()
	{
		return "
			select cmp.id,
				CONCAT_WS(' - ', cmp.codigo, cmp.nome) nome_com_codigo
			from competencias cmp
				join disciplinas_turmas dt on dt.id = cmp.id_disciplina_turma
			where dt.id = ?;
		";
	}

	/**
	 * Retorna módulos de curso do Moodle referentes a tarefas associadas à avaliação
	 * @return string
	 */
	public function mdl_modulos_avaliacoes()
	{
		return "
			select cm.id, asg.name
			from lmsinfne_mdl.mdl_course_modules cm
				join lmsinfne_mdl.mdl_modules m on m.id = cm.module
				join lmsinfne_mdl.mdl_assign asg on asg.id = cm.instance
				join avaliacoes_mdl_course_modules acm on acm.instance_mdl_course_modules = cm.instance
			where m.name = 'assign'
				and acm.id_avaliacao = ?
		";
	}

	/**
	 * Retorna o nome de uma avaliação específica
	 * @return string
	 */
	public function nome_avaliacao()
	{
		return "
			select CONCAT_WS(' / ', a.nome, asg.name) nome_avaliacao
			from avaliacoes a
				join avaliacoes_mdl_course_modules acm on acm.id_avaliacao = a.id
				join lmsinfne_mdl.mdl_assign asg on asg.id = acm.instance_mdl_course_modules
			where
				a.id = ?
				and exists (
					select 1 from v_rubricas_avaliacoes vra
					where vra.id_avaliacao = a.id
						and vra.id_mdl_gradingform_rubric_criteria = ?
				)
		";
	}

	/**
	 * Retorna dados de uma disciplina em uma turma específica
	 * @return string
	 */
	public function disciplina_disciplina_turma()
	{
		return "
			select d.id,
				d.nome,
				d.denominacao_bloco,
				b.id id_bloco,
				b.nome nome_bloco
			from disciplinas_turmas dt
				join disciplinas d on d.id = dt.id_disciplina
				join blocos b on b.id = d.id_bloco
			where dt.id = ?;
		";
	}

	/**
	 * Retorna dados da turma a partir de uma disciplina da própria turma
	 * @return string
	 */
	public function turma_disciplina_turma()
	{
		return "
			select t.id,
				t.nome,
				t.trimestre,
				t.ano,
				t.id_mdl_course_category,
				p.id id_programa,
				p.nome nome_programa,
				p.sigla sigla_programa,
				m.id id_modalidade,
				m.nome nome_modalidade,
				e.id id_escola,
				e.nome nome_escola,
				e.sigla sigla_escola
			from disciplinas_turmas dt
				join turmas t on t.id = dt.id_turma
				join programas p on p.id = t.id_programa
				join modalidades m on m.id = t.id_modalidade
				join escolas e on e.id = p.id_escola
			where dt.id = ?;
		";
	}

	/**
	 * Retorna estudantes integrantes de uma disciplina em uma turma
	 * @return string
	 */
	public function estudantes_disciplina_turma()
	{
		return "
			select CONCAT_WS(' ', usr.firstname, usr.lastname) nome_completo,
				usr.email,
				usr.username mdl_username,
				usr.id mdl_userid
			from lmsinfne_mdl.mdl_user usr
				join lmsinfne_mdl.mdl_role_assignments ra on ra.userid = usr.id
				join lmsinfne_mdl.mdl_role r on r.id = ra.roleid
				join lmsinfne_mdl.mdl_context cx on cx.id = ra.contextid
				join disciplinas_turmas dt on dt.id_mdl_course = cx.instanceid
			where r.archetype = 'student'
				and dt.id = ?
			order by nome_completo;
		";
	}

	/**
	 * Retorna avaliações de uma disciplina em uma turma,
	 * com informações sobre competências e subcompetências
	 * @return string
	 */
	public function avaliacoes_disciplina_turma()
	{
		return "
			select a.id id_avaliacao,
				a.nome nome_avaliacao,
				a.avaliacao_final,
				vra.id_mdl_gradingform_rubric_criteria,
				vra.rubrica,
				vra.ordem_rubrica,
				cmp.codigo codigo_competencia,
				cmp.nome nome_competencia,
				scmp.codigo_completo_calc codigo_subcompetencia,
				scmp.nome nome_subcompetencia,
				scmp.obrigatoria obrigatoria_subcompetencia
			from avaliacoes a
				left join v_rubricas_avaliacoes vra on vra.id_avaliacao = a.id
				left join subcompetencias_mdl_gradingform_rubric_criteria sgrc on sgrc.id_mdl_gradingform_rubric_criteria = vra.id_mdl_gradingform_rubric_criteria
				left join subcompetencias scmp on scmp.id = sgrc.id_subcompetencia
				left join competencias cmp on cmp.id = scmp.id_competencia
			where a.id_disciplina_turma = ?
			order by a.avaliacao_final, a.nome, scmp.codigo_completo_calc, vra.ordem_rubrica;
		";
	}

	/**
	 * Retorna os resultados de todas as avaliações ativas cadastradas
	 * em uma disciplina de uma turma, por estudante e rubrica
	 * @return string
	 */
	public function resultados_avaliacoes_disciplina_turma()
	{
		return "
			select vra.id_avaliacao,
				ag.userid,
				CONCAT_WS(' ', usr.firstname, usr.lastname) nome_completo,
				sgrc.id_mdl_gradingform_rubric_criteria,
				case when grl.score > 0 then 1 else 0 end demonstrada
			from v_rubricas_avaliacoes vra
				join subcompetencias_mdl_gradingform_rubric_criteria sgrc on sgrc.id_mdl_gradingform_rubric_criteria = vra.id_mdl_gradingform_rubric_criteria
				join lmsinfne_mdl.mdl_gradingform_rubric_criteria grc on grc.id = sgrc.id_mdl_gradingform_rubric_criteria
				join lmsinfne_mdl.mdl_grading_definitions gd on gd.id = grc.definitionid
				join lmsinfne_mdl.mdl_gradingform_rubric_levels grl on grl.criterionid = sgrc.id_mdl_gradingform_rubric_criteria
				join lmsinfne_mdl.mdl_grading_instances gin on gin.definitionid = gd.id
				join lmsinfne_mdl.mdl_assign_grades ag on ag.id = gin.itemid
				join lmsinfne_mdl.mdl_user usr on usr.id = ag.userid
				join lmsinfne_mdl.mdl_gradingform_rubric_fillings as grf on grf.instanceid = gin.id
					and grf.criterionid = grc.id
					and grf.levelid = grl.id
			where vra.id_disciplina_turma = ?
				and gin.status = 1
				and gin.id = (
					select gin2.id
					from lmsinfne_mdl.mdl_grading_definitions as gd2
						join lmsinfne_mdl.mdl_grading_instances as gin2 on gin2.definitionid = gd2.id
						join lmsinfne_mdl.mdl_assign_grades as ag2 on ag2.id = gin2.itemid
						join lmsinfne_mdl.mdl_gradingform_rubric_fillings as grf2 on grf2.instanceid = gin2.id
					where gd2.id = gd.id
						and gin2.status = gin.status
						and ag2.userid = ag.userid
						and grf2.criterionid = grc.id
						and grf2.levelid = grl.id
					order by ag2.timecreated desc
					limit 1
				);
		";
	}
}
