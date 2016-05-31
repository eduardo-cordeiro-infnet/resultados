<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Consultas_SQL {
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->database();
	}

	/**
	 * Retorna todas as categorias de classe do Moodle com o caminho hierárquico
	 * @return string
	 */
	public function classes_mdl_categorias_com_caminho()
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
			where c.depth = 4
			order by c.path;
		";
	}

	/**
	 * Retorna cursos do Moodle com o caminho hierárquico
	 * Se for informada categoria do Moodle, os cursos desta categoria são ordenados no início
	 * Por padrão, retorna todos os cursos do Moodle
	 * Mas se $curso_por_nome === true, deve ser informado o nome da disciplina para retornar apenas um curso
	 * @return string
	 */
	public function mdl_curso_com_caminho_mdl_categoria($curso_por_nome = false, $curso_por_id_mdl = false)
	{
		$sql = "
			select crs.id,
				CONCAT_WS(' > ', c6.name, c5.name, c4.name, c3.name, c2.name, c.name, crs.fullname) curso_com_caminho,
				case when ? in (select id from classes where id_mdl_course_category in (c6.id, c5.id, c4.id, c3.id, c2.id, c.id)) then 1 else 0 end turma
			from lmsinfne_mdl.mdl_course crs
				left join lmsinfne_mdl.mdl_course_categories c on c.id = crs.category
				left join lmsinfne_mdl.mdl_course_categories c2 on c2.id = c.parent
				left join lmsinfne_mdl.mdl_course_categories c3 on c3.id = c2.parent
				left join lmsinfne_mdl.mdl_course_categories c4 on c4.id = c3.parent
				left join lmsinfne_mdl.mdl_course_categories c5 on c5.id = c4.parent
				left join lmsinfne_mdl.mdl_course_categories c6 on c6.id = c5.parent
		";

		if ($curso_por_nome === true)
		{
			$sql .= "
				where FILTRAR_STRING_REGEXP(crs.fullname, null) like CONCAT('%', FILTRAR_STRING_REGEXP(?, null), '%')
				having turma = 1
				limit 1
			";
		}
		else if ($curso_por_id_mdl === true)
		{
			$sql .= "
				where crs.id = ?
			";
		}
		else
		{
			$sql .= "
				order by turma desc, c.path;
			";
		}

		return $sql;
	}

	/**
	 * Retorna módulos do Moodle com o caminho hierárquico.
	 * Se for informado o nome da avaliação, é retornado um único módulo de acordo com o nome e curso Moodle.
	 * Se for informada uma quantidade de instâncias de módulos, são retornadas todas as instâncias informadas.
	 * @return string
	 */
	public function mdl_modulo_com_caminho_mdl_categoria($nome = null, $qtd_instances = null)
	{
		$nome_numero = '';
		$comparacoes_nome_like = array('0 = 1');
		$comparacoes_nome_exato = array('0 = 1');

		if (isset($nome))
		{
			$nomes_tps = unserialize(NOMES_TESTES_PERFORMANCE);
			$nomes_ats = unserialize(NOMES_ASSESSMENT_FINAL);
			$nomes_tps_sem_caracteres_especiais = array_map(
				'strtolower',
				array_map_params($nomes_tps, 'preg_replace', array('/[^A-z]+/', ''), 2)
			);
			$nomes_assessments_sem_caracteres_especiais = array_map(
				'strtolower',
				array_map_params($nomes_ats, 'preg_replace', array('/[^A-z]+/', ''), 2)
			);

			$nome_sem_caracteres_especiais = strtolower(preg_replace('/[^A-z0-9]+/', '', $nome));
			$nome_numero = preg_replace('/[^0-9]+/', '', $nome);

			$nomes_avaliacoes_sem_caracteres_especiais = array();
			if (in_array($nome_sem_caracteres_especiais, $nomes_assessments_sem_caracteres_especiais))
			{
				$nomes_avaliacoes_sem_caracteres_especiais = $nomes_assessments_sem_caracteres_especiais;
			}
			else
			{
				$nomes_avaliacoes_sem_caracteres_especiais = $nomes_tps_sem_caracteres_especiais;
			}

			// Antes de executar a query principal, definir as variáveis necessárias
			$this->CI->db->query("
				set @nome = ?;
			", array($nome_sem_caracteres_especiais));

			foreach ($nomes_avaliacoes_sem_caracteres_especiais as $nome_av)
			{
				$comparacoes_nome_like[] = "
							FILTRAR_STRING_REGEXP(asg.name, null) like '%" . $nome_av . "%'";
				$comparacoes_nome_exato[] = "
							FILTRAR_STRING_REGEXP(asg.name, null) = '" . $nome_av . "'";
			}
		}


		$sql = "
			select cm.id,
				cm.instance,
				CONCAT_WS(' > ', c6.name, c5.name, c4.name, c3.name, c2.name, c.name, crs.fullname, cs.name, asg.name) modulo_com_caminho,
				case
					when '" . $nome_numero . "' <> ''
						and FILTRAR_STRING_REGEXP(asg.name, '[[:digit:]]') = '" . $nome_numero . "'
						and (
							" . implode(' or', $comparacoes_nome_like) . "
						)
					then 0
					when '" . $nome_numero . "' <> ''
						and FILTRAR_STRING_REGEXP(asg.name, '[[:digit:]]') = CONCAT('%', '" . $nome_numero . "' , '%')
					then 1
					when " . implode(' or', $comparacoes_nome_exato) . "
					then 2
					else 100
				end ordem_semelhanca
			from lmsinfne_mdl.mdl_course_modules cm
				join lmsinfne_mdl.mdl_modules m on m.id = cm.module
					and m.name = 'assign'
				join lmsinfne_mdl.mdl_assign asg on asg.id = cm.instance
				join lmsinfne_mdl.mdl_course_sections cs on cs.id = cm.section
				join lmsinfne_mdl.mdl_course crs on crs.id = cm.course
				left join lmsinfne_mdl.mdl_course_categories c on c.id = crs.category
				left join lmsinfne_mdl.mdl_course_categories c2 on c2.id = c.parent
				left join lmsinfne_mdl.mdl_course_categories c3 on c3.id = c2.parent
				left join lmsinfne_mdl.mdl_course_categories c4 on c4.id = c3.parent
				left join lmsinfne_mdl.mdl_course_categories c5 on c5.id = c4.parent
				left join lmsinfne_mdl.mdl_course_categories c6 on c6.id = c5.parent
		";

		if (isset($qtd_instances))
		{
			if ($qtd_instances > 0)
			{
				$sql .= "
					where cm.instance in (" . implode(',', array_fill(0, $qtd_instances, '?')) . ")
				";
			}
			else
			{
				// Se $qtd_instances == 0, não retornar nenhum registro
				$sql .= "
					where 1 = 0
				";
			}
		}
		else
		{
			$sql .= "
				where cm.course = ?
			";
		}

		if (isset($nome))
		{
		$sql .= "
			having ordem_semelhanca < 100
			order by ordem_semelhanca
			limit 1
		";
		}

		$sql .= "
		;";

		/*
		if(in_array($nome_sem_caracteres_especiais, $nomes_assessments_sem_caracteres_especiais))
		{
			$this->CI->load->helper('debug');
			var_dump_pre($sql);
			var_dump_pre(generate_call_trace());
			die();
		}
		//*/

		return $sql;
	}

	/**
	 * Retorna todas as disciplinas, com o nome do bloco, se houver
	 * Disciplinas de blocos que estejam associados à classe especificada são ordenadas no início
	 * @param boolean apenas_sem_classe não retornar disciplinas já associadas a esta classe
	 * @return string
	 */
	public function disciplinas_blocos_classe($apenas_sem_classe)
	{
		$sql = "
			select d.id,
				CONCAT(d.nome, case when b.id is not null then CONCAT(' (', b.nome, ')') end) disciplina_bloco,
				case when b.id
					in (select id_bloco_red from turmas where id_classe = ?)
				then 1 else 0 end bloco_classe
			from disciplinas d
				left join blocos b on b.id = d.id_bloco
				left join turmas t on t.id_classe = ?
					and t.id_disciplina = d.id
					and t.id <> ?";

		if ($apenas_sem_classe)
		{
			$sql .= "
			where t.id is null";
		}

		$sql .= "
			order by bloco_classe desc, b.id, d.nome;
		";

		return $sql;
	}

	/**
	 * Retorna disciplinas que estão associadas a classes no seguinte formato:
	 * {Sigla da escola} > {Programa} > {Classe} > {Bloco} > {Disciplina}
	 * @return string
	 */
	public function turmas_com_caminho($turma_especifica = false, $apenas_com_rubricas_subcompetencias = false, $sem_turma_selecionada = false)
	{
		$db = $this->CI->db;

		$db->select("t.id,
			CONCAT(
				CONCAT_WS(' > ', e.sigla, p.nome, c.nome, b.nome, d.nome),
					case
						when d.denominacao_bloco is not null then CONCAT(' (', d.denominacao_bloco, ')')
						else ''
					end
			) turma_com_caminho,
			CONCAT(e.sigla, p.nome, c.nome, b.nome) bloco_com_caminho,
			d.denominacao_bloco"
		);

		$db
			->from('turmas t')
			->join('disciplinas d', 'd.id = t.id_disciplina')
			->join('blocos b', 'b.id = d.id_bloco')
			->join('classes c', 'c.id = t.id_classe')
			->join('programas p', 'p.id = c.id_programa')
			->join('escolas e', 'e.id = p.id_escola')
		;


		//Se for informada uma turma específica, o campo de turma selecionada é necessário
		if (!$sem_turma_selecionada || $turma_especifica)
		{
			$db
				->select('case when t.id = ? then 1 else 0 end turma_selecionada', false)
				->order_by('turma_selecionada', 'desc')
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
					where cmp.id_turma = t.id
				)'
			);
		}

		if ($turma_especifica)
		{
			$db->having('turma_selecionada', 1);
		}

		return $db->get_compiled_select();
	}

	/**
	 * Retorna competências de uma turma específica
	 * @return string
	 */
	public function competencias_turma()
	{
		return "
			select cmp.id,
				CONCAT_WS(' - ', cmp.codigo, cmp.nome) nome_com_codigo
			from competencias cmp
				join turmas t on t.id = cmp.id_turma
			where t.id = ?;
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
					and m.name = 'assign'
				join lmsinfne_mdl.mdl_assign asg on asg.id = cm.instance
				join avaliacoes_mdl_course_modules acm on acm.instance_mdl_course_modules = cm.instance
			where acm.id_avaliacao = ?
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
	 * Retorna dados da classe a partir de uma turma
	 * @return string
	 */
	public function dados_classe()
	{
		return "
			select c.nome,
				c.trimestre,
				c.ano,
				c.id_mdl_course_category,
				p.id id_programa,
				p.nome nome_programa,
				p.sigla sigla_programa,
				m.id id_modalidade,
				m.nome nome_modalidade,
				e.id id_escola,
				e.nome nome_escola,
				e.sigla sigla_escola
			from classes c
				join programas p on p.id = c.id_programa
				join modalidades m on m.id = c.id_modalidade
				join escolas e on e.id = p.id_escola
			where c.id = ?;
		";
	}

	/**
	 * Retorna estudantes de uma turma
	 * @return string
	 */
	public function estudantes_turma()
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
				join turmas t on t.id_mdl_course = cx.instanceid
			where r.archetype = 'student'
				and t.id = ?
			order by nome_completo;
		";
	}

	/**
	 * Retorna avaliações de uma turma, com informações
	 * sobre competências e subcompetências
	 * @return string
	 */
	public function avaliacoes_turma()
	{
		return "
			select a.id id_avaliacao,
				a.nome nome_avaliacao,
				a.avaliacao_final,
				vra.id_mdl_gradingform_rubric_criteria,
				vra.rubrica,
				vra.ordem_rubrica,
				cmp.id id_competencia,
				cmp.codigo codigo_competencia,
				cmp.nome nome_competencia,
				scmp.id id_subcompetencia,
				scmp.codigo_completo_calc codigo_subcompetencia,
				scmp.nome nome_subcompetencia,
				scmp.obrigatoria obrigatoria_subcompetencia
			from avaliacoes a
				left join v_rubricas_avaliacoes vra on vra.id_avaliacao = a.id
				left join subcompetencias_mdl_gradingform_rubric_criteria sgrc on sgrc.id_mdl_gradingform_rubric_criteria = vra.id_mdl_gradingform_rubric_criteria
				left join subcompetencias scmp on scmp.id = sgrc.id_subcompetencia
				left join competencias cmp on cmp.id = scmp.id_competencia
			where a.id_turma = ?
			order by a.avaliacao_final, a.nome, scmp.codigo_completo_calc, vra.ordem_rubrica;
		";
	}

	/**
	 * Retorna os resultados de todas as avaliações ativas
	 * cadastradas em uma turma, por estudante e rubrica
	 * @return string
	 */
	public function resultados_avaliacoes_turma()
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
			where vra.id_turma = ?
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

	/**
	 * Retorna registros de módulo do Moodle a partir do valor do campo instance
	 * Se for informado $qtd_instances, são buscar
	 * @return string
	 */
	public function mdl_modulo_instance($qtd_instances = null)
	{
		$sql = "
			SELECT cm.*
			FROM lmsinfne_mdl.mdl_course_modules AS cm
				JOIN lmsinfne_mdl.mdl_modules AS m ON m.id = cm.module
					AND m.name = 'assign'
			WHERE cm.instance";

		if ($qtd_instances > 0)
		{
			$sql .= " IN (" . implode(',', array_fill(0, $qtd_instances, '?')) . ")";
		}
		else
		{
			$sql .= " = ?";
		}

		$sql .= ";";

		return $sql;
	}

	/**
	 * Retorna rubricas do Moodle a partir do valor do campo instance de módulos
	 * @return string
	 */
	public function mdl_rubricas_instance($qtd_instances)
	{
		$sql = "
			SELECT vr.id_mdl_gradingform_rubric_criteria AS mdl_id,
				   vr.rubrica AS descricao,
				   vr.ordem_rubrica AS ordem,
				   vr.*
			FROM v_rubricas AS vr
			WHERE vr.instance_mdl_course_module";

		if ($qtd_instances > 0)
		{
			$sql .= " IN (" . implode(',', array_fill(0, $qtd_instances, '?')) . ")";
		}
		else
		{
			$sql .= " = ?";
		}

		$sql .= ";";

		return $sql;
	}
}
