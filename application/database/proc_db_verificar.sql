drop procedure if exists `db_verificar`;
delimiter //
create procedure `db_verificar`()
	language sql
	not deterministic
	contains sql
	sql security definer
	comment ''
begin
	declare secao varchar(200);

	set secao = 'Consistência com Moodle';

		select secao, 'turmas.id_mdl_course = cursos inexistentes no Moodle';
		select *
		from turmas t
		where t.id_mdl_course is not null
			and not exists (
				select 1 from lmsinfne_mdl.mdl_course crs
				where crs.id = t.id_mdl_course
			);

		select secao, 'classes.id_mdl_course_category = categorias inexistentes no Moodle';
		select *
		from classes c
		where c.id_mdl_course_category is not null
			and not exists (
				select 1 from lmsinfne_mdl.mdl_course_categories cc
				where cc.id = c.id_mdl_course_category
			);

		select secao, 'avaliacoes_mdl_course_modules.instance_mdl_course_modules = módulos inexistentes no Moodle';
		select *
		from avaliacoes_mdl_course_modules acm
		where not exists (
			select 1 from lmsinfne_mdl.mdl_course_modules cm
			where cm.instance = acm.instance_mdl_course_modules
		);

		select secao, 'subcompetencias_mdl_gradingform_rubric_criteria.id_mdl_gradingform_rubric_criteria = rubricas inexistentes no Moodle';
		select *
		from subcompetencias_mdl_gradingform_rubric_criteria scmpgrc
		where not exists (
			select 1 from lmsinfne_mdl.mdl_gradingform_rubric_criteria grc
			where grc.id = scmpgrc.id_mdl_gradingform_rubric_criteria
		);

	set secao = 'Consistência de campos atualizados por trigger';

		select secao, 'classes.qtd_disciplinas_calc';
		select c.*
		from classes c
			left join (
				select id_classe,
					COUNT(1) cnt
	            from turmas
	        	group by id_classe
			) t on t.id_classe = c.id
		where c.qtd_disciplinas_calc <> COALESCE(t.cnt, 0);

		select secao, 'subcompetencias.codigo_completo_calc';
		select *
		from subcompetencias scmp
			join competencias cmp on cmp.id = scmp.id_competencia
		where scmp.codigo_completo_calc <> CONCAT(cmp.codigo, '.', scmp.codigo, case when scmp.obrigatoria = 1 then '*' else '' end);

		select secao, 'turmas.id_bloco_red';
		select *
		from turmas t
			join disciplinas d on d.id = t.id_disciplina
			left join blocos b on b.id = d.id_bloco
		where b.id <> t.id_bloco_red;

		select secao, 'classes.id_escola_red';
		select *
		from classes c
			join programas p on p.id = c.id_programa
			join escolas e on e.id = p.id_escola
		where e.id <> c.id_escola_red;

		select secao, 'subcompetencias.id_turma_red';
		select *
		from subcompetencias scmp
			join competencias cmp on cmp.id = scmp.id_competencia
		where scmp.id_turma_red <> cmp.id_turma;
end//
delimiter ;
