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
			->join('escolas e', 'e.id = f.id_escola')
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
	 * Retorna os resultados de todas as avaliações ativas cadastradas
	 * em uma disciplina de uma turma, por estudante e rubrica
	 * @return string
	 */
	public function resultados_avaliacoes_disciplina_turma()
	{
		return "
			select a.id id_avaliacao,
				a.nome nome_avaliacao,
				acm.id id_avaliacoes_mdl_course_modules,
				acm.id_mdl_course_module,
				acm.id_mdl_assign,
				acm.name_mdl_assign,
				est.id mdl_userid,
				est.nome_completo nome_completo,
				est.email,
				est.username mdl_username,
				est.id mdl_userid,
				grc.id id_mdl_gradingform_rubric_criteria,
				grc.description rubrica,
				scmp.id id_subcompetencia,
				scmp.codigo_completo_calc codigo_subcompetencia,
				scmp.nome nome_subcompetencia,
				scmp.codigo_competencia,
				scmp.nome_competencia,
				sub.attemptnumber num_tentativa_ultima_entrega,
				grl.id id_mdl_gradingform_rubric_levels,
				grl.score pontuacao_rubrica,
				grl.attemptnumber num_tentativa_ultima_correcao
			from avaliacoes a
				join disciplinas_turmas dt on dt.id	= a.id_disciplina_turma
				left join (
					select acm.id,
						acm.id_avaliacao,
						cm.id id_mdl_course_module,
						asg.id id_mdl_assign,
						asg.name name_mdl_assign
					from avaliacoes_mdl_course_modules acm
						join lmsinfne_mdl.mdl_course_modules cm on cm.instance = acm.instance_mdl_course_modules
						join lmsinfne_mdl.mdl_modules m on m.id = cm.module
						join lmsinfne_mdl.mdl_assign asg on asg.id = cm.instance
					where m.name = 'assign'
				) acm on acm.id_avaliacao = a.id
				left join (
					select usr.id,
						CONCAT_WS(' ', usr.firstname, usr.lastname) nome_completo,
						usr.email,
						usr.username,
						cx.instanceid
					from lmsinfne_mdl.mdl_user usr
						join lmsinfne_mdl.mdl_role_assignments ra on ra.userid = usr.id
						join lmsinfne_mdl.mdl_role r on r.id = ra.roleid
						join lmsinfne_mdl.mdl_context cx on cx.id = ra.contextid
					where r.archetype = 'student'
				) est on est.instanceid = dt.id_mdl_course
				left join (
					select grc.id,
						grc.description,
						grc.sortorder,
						gd.id id_mdl_grading_definitions,
						cx.instanceid instanceid_mdl_context
					from lmsinfne_mdl.mdl_gradingform_rubric_criteria grc
						join lmsinfne_mdl.mdl_grading_definitions gd on gd.id = grc.definitionid
						join lmsinfne_mdl.mdl_grading_areas ga on ga.id = gd.areaid
						join lmsinfne_mdl.mdl_context cx on cx.id = ga.contextid
				) grc on grc.instanceid_mdl_context = acm.id_mdl_course_module
				left join (
					select scmp.id,
						scmp.codigo_completo_calc,
						scmp.nome,
						cmp.codigo codigo_competencia,
						cmp.nome nome_competencia,
						sgrc.id_mdl_gradingform_rubric_criteria
					from subcompetencias scmp
						join competencias cmp on cmp.id = scmp.id_competencia
						join subcompetencias_mdl_gradingform_rubric_criteria sgrc on sgrc.id_subcompetencia = scmp.id
				) scmp on scmp.id_mdl_gradingform_rubric_criteria = grc.id
				left join lmsinfne_mdl.mdl_assign_submission sub on sub.id = (
					select sub2.id
					from lmsinfne_mdl.mdl_assign_submission sub2
					where sub2.assignment = acm.id_mdl_assign
						and sub2.userid = est.id
						and sub2.status = 'submitted'
					order by sub2.attemptnumber desc
				)
				left join (
					select grl.id,
						gin.id id_mdl_grading_instances,
						grl.score,
						ag.userid,
						ag.attemptnumber,
						gin.definitionid,
						gin.status,
						grf.criterionid
					from (lmsinfne_mdl.mdl_gradingform_rubric_levels grl,
						lmsinfne_mdl.mdl_grading_instances gin)
						join lmsinfne_mdl.mdl_assign_grades ag on ag.id = gin.itemid
						join lmsinfne_mdl.mdl_gradingform_rubric_fillings grf on grf.instanceid = gin.id
							and grf.levelid = grl.id
					where gin.status = 1
				) grl on grl.definitionid = grc.id_mdl_grading_definitions
					and grl.criterionid = grc.id
					and grl.userid = est.id
					and grl.id_mdl_grading_instances = (
						select gin2.id
						from lmsinfne_mdl.mdl_grading_definitions gd2
							join lmsinfne_mdl.mdl_grading_instances gin2 on gin2.definitionid = gd2.id
							join lmsinfne_mdl.mdl_assign_grades ag2 on ag2.id = gin2.itemid
							join lmsinfne_mdl.mdl_gradingform_rubric_fillings grf2 on grf2.instanceid = gin2.id
						where gd2.id = grc.id_mdl_grading_definitions
							and gin2.status = grl.status
							and ag2.userid = est.id
							and grf2.criterionid = grc.id
							and grf2.levelid = grl.id
						order by ag2.attemptnumber desc
						limit 1
					)
			where a.id_disciplina_turma = ?
			order by case when nome_avaliacao = 'Assessment final' then 1 else 0 end, nome_avaliacao, nome_completo, grc.sortorder
			;
		";
	}
}
