<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Consultas SQL
 *
 * Agrupamento de todas as consultas SQL do sistema.
 *
 */
class Consultas_SQL {

	/**
	 * Retornar todas as categorias do Moodle com o caminho hierárquico
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
	 * Retornar todos os cursos do Moodle com o caminho hierárquico
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
	 * Retornar todas as disciplinas, com o nome do bloco, se houver
	 * Disciplinas de blocos que estejam associados à turma especificada são ordenadas no início
	 * @param boolean apenas_disciplinas_sem_turma não retornar disciplinas já associadas a esta turma
	 * @return string
	 */
	public function disciplinas_blocos_turma($apenas_disciplinas_sem_turma)
	{
		$sql = "
			select d.id,
				CONCAT(d.nome, case when b.id is not null then CONCAT(' (', b.nome, ')') end) disciplina_com_caminho,
				case when b.id
					in (select id_bloco_red from disciplinas_turmas where id_turma = ?)
				then 1 else 0 end bloco_turma
			from disciplinas d
				left join blocos b on b.id = d.id_bloco
				left join disciplinas_turmas dt on dt.id_turma = ?
					and dt.id_disciplina = d.id
					and dt.id <> ?";

		if ($apenas_disciplinas_sem_turma)
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
	 * Retornar disciplinas que estão associadas a turmas no seguinte formato:
	 * {Sigla da escola} > {Formação} > {Turma} > {Bloco} > {Disciplina}
	 * @return string
	 */
	public function disciplinas_turmas_com_caminho($retornar_disciplina_turma_especifica = false)
	{
		$sql = "
			select dt.id,
				CONCAT(
					CONCAT_WS(' > ', e.sigla, f.nome, t.nome, b.nome, d.nome),
						case
							when d.denominacao_bloco is not null then CONCAT(' (', d.denominacao_bloco, ')')
							else ''
						end
				) disciplina_turma_com_caminho,
				CONCAT(e.sigla, f.nome, t.nome, b.nome) bloco_com_caminho,
				d.denominacao_bloco,
				case when dt.id = ? then 1 else 0 end disciplina_turma_selecionada
			from disciplinas_turmas dt
				join disciplinas d on d.id = dt.id_disciplina
				join blocos b on b.id = d.id_bloco
				join turmas t on t.id = dt.id_turma
				join formacoes f on f.id = t.id_formacao
				join escolas e on e.id = f.id_escola
		";

		if ($retornar_disciplina_turma_especifica)
		{
			$sql .= "
			having disciplina_turma_selecionada = 1
			";
		}

		$sql .= "
			order by disciplina_turma_selecionada desc, bloco_com_caminho, denominacao_bloco;
		";

		return $sql;
	}

	/**
	 * Retornar competências de uma disciplinas associada a uma turma específica
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
	 * Retornar módulos de curso do Moodle referentes a tarefas associadas à avaliação
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
	 * Retornar o nome de uma avaliação específica
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
}
